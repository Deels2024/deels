<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\RateLimiter;

class LikeActionRateLimiter
{
    private const DECAY_SECONDS = 60;

    public function hit(User $user): ?string
    {
        $attempts = RateLimiter::hit($this->key($user), self::DECAY_SECONDS);

        return $attempts > (int) config('action_rate_limits.likes.per_minute', 50)
            ? 'like_rate'
            : null;
    }

    public function clear(User $user): void
    {
        RateLimiter::clear($this->key($user));
    }

    private function key(User $user): string
    {
        return 'user-action:like:'.$user->id;
    }
}
