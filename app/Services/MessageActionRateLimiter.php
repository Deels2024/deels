<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class MessageActionRateLimiter
{
    private const DECAY_SECONDS = 60;

    /**
     * Records a message attempt and returns the violated rule, if any.
     */
    public function hit(User $user, string $message): ?string
    {
        $messageAttempts = RateLimiter::hit(
            'user-action:message:'.$user->id,
            self::DECAY_SECONDS
        );

        if ($messageAttempts > (int) config('action_rate_limits.messages.per_minute', 150)) {
            return 'message_rate';
        }

        $normalizedMessage = $this->normalize($message);
        if ($normalizedMessage === '') {
            return null;
        }

        $repeatAttempts = RateLimiter::hit(
            'user-action:message-repeat:'.$user->id.':'.hash('sha256', $normalizedMessage),
            self::DECAY_SECONDS
        );

        if ($repeatAttempts > (int) config('action_rate_limits.messages.repeat_per_minute', 5)) {
            return 'message_repeat';
        }

        return null;
    }

    public function clear(User $user, ?string $message = null): void
    {
        RateLimiter::clear('user-action:message:'.$user->id);

        if ($message !== null && $this->normalize($message) !== '') {
            RateLimiter::clear(
                'user-action:message-repeat:'.$user->id.':'.hash('sha256', $this->normalize($message))
            );
        }
    }

    private function normalize(string $message): string
    {
        $message = preg_replace('/\s+/u', ' ', trim($message)) ?? trim($message);

        return mb_strtolower($message);
    }
}
