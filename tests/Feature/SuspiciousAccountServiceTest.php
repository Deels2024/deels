<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Jobs\SendTGSuspiciousAccountModeration;
use App\Models\User;
use App\Services\SuspiciousAccountService;
use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SuspiciousAccountServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->boolean('is_suspicious')->default(false);
            $table->unsignedTinyInteger('suspicious_violations')->default(0);
            $table->boolean('suspicious_moderation_pending')->default(false);
            $table->timestamp('suspicious_moderation_requested_at')->nullable();
            $table->timestamp('suspicious_blocked_until')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('users_activation', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type');
            $table->boolean('is_verified')->nullable();
            $table->timestamps();
        });
    }

    public function test_it_returns_email_requirement_first(): void
    {
        $user = User::create(['is_suspicious' => true]);

        self::assertSame(
            ['need_email' => 'Укажите почту'],
            app(SuspiciousAccountService::class)->needActions($user)
        );
    }

    public function test_third_restricted_attempt_starts_manual_moderation(): void
    {
        Queue::fake();
        Carbon::setTestNow('2026-07-17 12:00:00');
        $user = User::create([
            'email' => 'user@example.test',
            'phone' => '+79990000000',
            'is_suspicious' => true,
        ]);
        $user->emailVerify()->create(['type' => 'email', 'is_verified' => true]);
        $user->phoneVerify()->create(['type' => 'phone', 'is_verified' => true]);
        $service = app(SuspiciousAccountService::class);

        $service->markSuspicious($user);
        $first = $service->restriction($user->fresh());
        self::assertStringContainsString('через 1 ч.', $first['message']);
        self::assertStringNotContainsString('0 мин.', $first['message']);
        self::assertSame(3600, $first['retry_after']);
        self::assertSame(1, $user->fresh()->suspicious_violations);

        Carbon::setTestNow('2026-07-17 12:30:01');
        $second = $service->restriction($user->fresh());
        self::assertStringContainsString('через 30 мин.', $second['message']);
        self::assertStringNotContainsString('0 ч.', $second['message']);
        self::assertSame(2, $user->fresh()->suspicious_violations);

        $result = $service->restriction($user->fresh());

        self::assertSame(SuspiciousAccountService::MODERATION_MESSAGE, $result['message']);
        self::assertTrue($user->fresh()->suspicious_moderation_pending);
        self::assertSame(3, $user->fresh()->suspicious_violations);
        Queue::assertPushed(SendTGSuspiciousAccountModeration::class, 1);
        Carbon::setTestNow();
    }

    public function test_successful_verification_can_clear_all_suspicious_state(): void
    {
        $user = User::create([
            'is_suspicious' => true,
            'suspicious_violations' => 3,
            'suspicious_moderation_pending' => true,
            'suspicious_moderation_requested_at' => now(),
            'suspicious_blocked_until' => now()->addHour(),
        ]);

        $user->clearSuspiciousStatus();
        $user->refresh();

        self::assertFalse($user->is_suspicious);
        self::assertFalse($user->suspicious_moderation_pending);
        self::assertSame(0, $user->suspicious_violations);
        self::assertNull($user->suspicious_moderation_requested_at);
        self::assertNull($user->suspicious_blocked_until);
    }
}
