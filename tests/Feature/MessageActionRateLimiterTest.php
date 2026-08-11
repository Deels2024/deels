<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Services\MessageActionRateLimiter;
use App\Services\SuspiciousAccountService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MessageActionRateLimiterTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('users', function (Blueprint $table): void {
            $table->id();
            $table->boolean('is_suspicious')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        Cache::flush();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');
        parent::tearDown();
    }

    public function test_sixth_normalized_identical_message_marks_user_as_suspicious(): void
    {
        $user = User::create(['is_suspicious' => false]);
        $limiter = app(MessageActionRateLimiter::class);

        self::assertNull($limiter->hit($user, 'Тестовое сообщение'));
        self::assertNull($limiter->hit($user, ' тестовое сообщение '));
        self::assertNull($limiter->hit($user, 'ТЕСТОВОЕ СООБЩЕНИЕ'));
        self::assertNull($limiter->hit($user, "Тестовое   сообщение"));
        self::assertNull($limiter->hit($user, 'Тестовое сообщение'));

        $reason = $limiter->hit($user, 'Тестовое сообщение');
        self::assertSame('message_repeat', $reason);

        app(SuspiciousAccountService::class)->markSuspicious($user);
        self::assertTrue($user->fresh()->is_suspicious);
    }
}
