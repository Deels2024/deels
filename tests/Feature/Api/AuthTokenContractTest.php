<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class AuthTokenContractTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();

        $this->createCharacterizationSchema();
        EloquentModel::unsetEventDispatcher();
    }

    public function test_auth_token_success_contract(): void
    {
        $user = User::create([
            'name' => 'Auth User',
            'email' => 'auth@example.test',
            'password' => Hash::make('secret-password'),
        ]);

        $response = $this
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 Chrome Mobile',
            ])
            ->postJson('/api/auth_token', [
                'email' => 'auth@example.test',
                'password' => 'secret-password',
                'device_name' => 'iphone',
            ]);

        $response
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'access_token',
                'token_type',
            ])
            ->assertJson([
                'success' => true,
                'token_type' => 'Bearer',
            ]);

        $payload = $response->json();

        self::assertIsString($payload['access_token']);
        self::assertNotSame('', $payload['access_token']);
        self::assertSame(1, PersonalAccessToken::query()->where('tokenable_id', $user->id)->count());

        $user->refresh();
        $userData = is_string($user->user_data) ? json_decode($user->user_data, true) : $user->user_data;

        self::assertSame('127.0.0.1', $user->ip_address);
        self::assertSame('Chrome', $userData['browser']);
        self::assertSame('Mobile', $userData['device']);
    }

    public function test_auth_token_accepts_username_in_email_field(): void
    {
        $user = User::create([
            'name' => 'Username Auth User',
            'username' => 'auth-by-username',
            'email' => 'username-auth@example.test',
            'password' => Hash::make('secret-password'),
        ]);

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/auth_token', [
                'email' => 'auth-by-username',
                'password' => 'secret-password',
                'device_name' => 'iphone',
            ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'token_type' => 'Bearer',
            ]);

        self::assertSame(1, PersonalAccessToken::query()->where('tokenable_id', $user->id)->count());
    }

    public function test_auth_token_invalid_password_contract(): void
    {
        User::create([
            'name' => 'Auth User',
            'email' => 'auth@example.test',
            'password' => Hash::make('secret-password'),
        ]);

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/auth_token', [
                'email' => 'auth@example.test',
                'password' => 'wrong-password',
                'device_name' => 'iphone',
            ]);

        $response
            ->assertOk()
            ->assertJsonStructure(['error'])
            ->assertJsonMissingPath('success')
            ->assertJsonMissingPath('access_token')
            ->assertJson([
                'error' => 'Пользователь не найден или неверный пароль.',
            ]);
    }

    public function test_auth_token_escalates_every_fourth_account_lockout_to_one_day(): void
    {
        Carbon::setTestNow('2026-07-15 12:00:00');

        User::create([
            'name' => 'Rate Limited User',
            'username' => 'rate-limited-user',
            'email' => 'rate-limited@example.test',
            'password' => Hash::make('secret-password'),
            'meta_data' => ['existing_key' => 'must-be-preserved'],
        ]);

        try {
            for ($lockout = 1; $lockout <= 4; $lockout++) {
                for ($attempt = 1; $attempt <= 5; $attempt++) {
                    $response = $this->postJson('/api/auth_token', [
                        'email' => 'rate-limited-user',
                        'password' => 'wrong-password',
                        'device_name' => 'iphone',
                    ]);
                }

                $response
                    ->assertStatus(429)
                    ->assertJson([
                        'retry_after' => $lockout === 4 ? 86400 : 1800,
                    ]);

                if ($lockout < 4) {
                    Carbon::setTestNow(now()->addMinutes(31));
                }
            }

            $user = User::where('username', 'rate-limited-user')->firstOrFail();
            self::assertSame('must-be-preserved', $user->meta_data['existing_key']);
            self::assertSame(0, $user->meta_data['login_rate_limit']['lockouts']);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_auth_token_banned_user_contract(): void
    {
        $bannedTill = now()->addDay()->setMicrosecond(0);
        $user = User::create([
            'name' => 'Banned User',
            'email' => 'banned@example.test',
            'password' => Hash::make('secret-password'),
            'banned_till' => $bannedTill,
        ]);

        $response = $this
            ->withHeaders(['Accept' => 'application/json'])
            ->postJson('/api/auth_token', [
                'email' => 'banned@example.test',
                'password' => 'secret-password',
                'device_name' => 'iphone',
            ]);

        $response
            ->assertStatus(403)
            ->assertJsonStructure([
                'error',
                'banned',
                'banned_till',
            ])
            ->assertJson([
                'banned' => true,
                'banned_till' => $bannedTill->toDateTimeString(),
            ]);

        self::assertSame(1, PersonalAccessToken::query()->where('tokenable_id', $user->id)->count());
        self::assertStringContainsString($bannedTill->format('Y-m-d H:i:s'), $response->json('error'));
    }
}
