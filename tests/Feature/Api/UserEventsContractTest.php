<?php

declare(strict_types=1);

namespace Tests\Feature\Api;

use App\Models\User;
use App\Models\UserEvent;
use App\Services\ApiAccountInfoService;
use Laravel\Sanctum\Sanctum;
use Tests\Feature\Support\CreatesCharacterizationSchema;
use Tests\TestCase;

class UserEventsContractTest extends TestCase
{
    use CreatesCharacterizationSchema;

    protected function setUp(): void
    {
        parent::setUp();
        $this->createCharacterizationSchema();
    }

    public function test_get_user_returns_only_pending_unexpired_events_to_the_owner(): void
    {
        $user = $this->user('Владелец');
        Sanctum::actingAs($user);

        $pending = $this->event($user, now()->addDays(14));
        $this->event($user, now()->subSecond(), 'expired');
        $this->event($user, now()->addDays(14), 'dismissed', now());

        $data = app(ApiAccountInfoService::class)->build($user->id, true);

        self::assertCount(1, $data['events']);
        self::assertSame($pending->id, $data['events'][0]['id']);
        self::assertSame('challenge_win', $data['events'][0]['result']);
        self::assertSame('fireworks', $data['events'][0]['data']['presentation']['background']);
    }

    public function test_dismiss_endpoint_hides_event_from_get_user(): void
    {
        $user = $this->user('Владелец');
        Sanctum::actingAs($user);
        $event = $this->event($user, now()->addDays(14));

        $this->postJson('/api/user/events/'.$event->id.'/dismiss')
            ->assertOk()
            ->assertJson(['success' => true]);

        self::assertNotNull($event->fresh()->dismissed_at);
        self::assertSame(0, $user->events()->pending()->count());
    }

    public function test_event_cannot_be_dismissed_by_another_user(): void
    {
        $owner = $this->user('Владелец');
        $stranger = $this->user('Другой');
        Sanctum::actingAs($stranger);
        $event = $this->event($owner, now()->addDays(14));

        $this->postJson('/api/user/events/'.$event->id.'/dismiss')->assertNotFound();
        self::assertNull($event->fresh()->dismissed_at);
    }

    private function user(string $name): User
    {
        return User::create([
            'name' => $name,
            'email' => md5($name).'@example.test',
            'password' => 'secret',
        ]);
    }

    private function event(
        User $user,
        $expiresAt,
        string $sourceId = '1',
        $dismissedAt = null
    ): UserEvent {
        return UserEvent::create([
            'user_id' => $user->id,
            'type' => 'contest_result',
            'source_type' => 'challenge',
            'source_id' => (int) crc32($sourceId),
            'result' => 'challenge_win',
            'data' => [
                'presentation' => ['background' => 'fireworks', 'closable' => true],
            ],
            'expires_at' => $expiresAt,
            'dismissed_at' => $dismissedAt,
        ]);
    }
}
