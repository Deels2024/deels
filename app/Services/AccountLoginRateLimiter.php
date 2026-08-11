<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AccountLoginRateLimiter
{
    private const MAX_ATTEMPTS = 5;
    private const LONG_LOCKOUT_AFTER = 4;
    private const SHORT_LOCKOUT_MINUTES = 30;
    private const LONG_LOCKOUT_MINUTES = 1440;

    public function blockedFor(User $user): int
    {
        $metaData = User::query()->find($user->getKey())?->meta_data ?? [];
        $blockedUntil = $metaData['login_rate_limit']['blocked_until'] ?? null;

        if (!$blockedUntil) {
            return 0;
        }

        return max(0, Carbon::parse($blockedUntil)->timestamp - now()->timestamp);
    }

    public function recordFailure(User $user): int
    {
        return DB::transaction(function () use ($user): int {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $metaData = $lockedUser->meta_data ?? [];
            $state = $metaData['login_rate_limit'] ?? [];
            $blockedUntil = $state['blocked_until'] ?? null;

            if ($blockedUntil && Carbon::parse($blockedUntil)->isFuture()) {
                return Carbon::parse($blockedUntil)->timestamp - now()->timestamp;
            }

            $state['attempts'] = (int) ($state['attempts'] ?? 0) + 1;

            if ($state['attempts'] < self::MAX_ATTEMPTS) {
                $metaData['login_rate_limit'] = $state;
                $lockedUser->meta_data = $metaData;
                $lockedUser->save();

                return 0;
            }

            $state['attempts'] = 0;
            $state['lockouts'] = (int) ($state['lockouts'] ?? 0) + 1;
            $minutes = self::SHORT_LOCKOUT_MINUTES;

            if ($state['lockouts'] >= self::LONG_LOCKOUT_AFTER) {
                $minutes = self::LONG_LOCKOUT_MINUTES;
                $state['lockouts'] = 0;
            }

            $state['blocked_until'] = now()->addMinutes($minutes)->toIso8601String();
            $metaData['login_rate_limit'] = $state;
            $lockedUser->meta_data = $metaData;
            $lockedUser->save();

            return $minutes * 60;
        });
    }

    public function clearFailures(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->getKey());
            $metaData = $lockedUser->meta_data ?? [];

            if (!isset($metaData['login_rate_limit'])) {
                return;
            }

            $metaData['login_rate_limit']['attempts'] = 0;
            $lockedUser->meta_data = $metaData;
            $lockedUser->save();
        });
    }
}
