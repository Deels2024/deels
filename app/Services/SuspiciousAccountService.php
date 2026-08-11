<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\SendTGSuspiciousAccountModeration;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class SuspiciousAccountService
{
    public const MODERATION_MESSAGE = 'Ваша активность слишком великая, наш сервер перегрелся и ему нужно отдохнуть. Все возможности восстановятся после ручной модерации';

    public function markSuspicious(User $user): void
    {
        if ($user->is_suspicious && ($user->suspicious_moderation_pending || $user->suspicious_blocked_until?->isFuture())) {
            return;
        }

        DB::transaction(function () use ($user): void {
            /** @var User $lockedUser */
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->suspicious_moderation_pending || $lockedUser->suspicious_blocked_until?->isFuture()) {
                return;
            }

            $lockedUser->is_suspicious = true;
            $lockedUser->suspicious_blocked_until = now()->addHour();
            $lockedUser->save();
        });
    }

    public function needActions(User $user): array
    {
        if (! $user->is_suspicious) {
            return [];
        }

        if (empty($user->email)) {
            return ['need_email' => 'Укажите почту'];
        }

        if ($user->emailVerificationPending()) {
            return ['need_email_verify' => 'Подтвердите почту'];
        }

        if (empty($user->phone)) {
            return ['need_phone' => 'Укажите телефон'];
        }

        if ($user->phoneVerificationPending()) {
            return ['need_phone_verify' => 'Подтвердите телефон'];
        }

        return [];
    }

    public function restriction(User $user): ?array
    {
        $needActions = $this->needActions($user);
        if ($needActions !== []) {
            $key = array_key_first($needActions);

            return $this->payload($needActions[$key], $needActions);
        }

        if ($user->suspicious_moderation_pending) {
            return $this->payload(self::MODERATION_MESSAGE, [], $user);
        }

        if ($user->suspicious_blocked_until?->isPast()) {
            $user->forceFill([
                'is_suspicious' => false,
                'suspicious_blocked_until' => null,
            ])->save();

            return null;
        }

        if (! $user->suspicious_blocked_until) {
            $this->markSuspicious($user);
            $user->refresh();
        }

        $user = $this->recordViolation($user);
        if ($user->suspicious_moderation_pending) {
            return $this->payload(self::MODERATION_MESSAGE, [], $user);
        }

        $minutes = max(1, (int) ceil(now()->diffInSeconds($user->suspicious_blocked_until, false) / 60));
        $hours = intdiv($minutes, 60);
        $remainingTime = $hours > 0
            ? sprintf('%d ч.', $hours)
            : sprintf('%d мин.', $minutes);
        $message = sprintf(
            'Ваша активность слишком великая, наш сервер перегрелся и ему нужно отдохнуть. Все возможности восстановятся через %s',
            $remainingTime
        );

        return $this->payload($message, [], $user, $minutes * 60);
    }

    private function recordViolation(User $user): User
    {
        $sendNotification = false;
        $user = DB::transaction(function () use ($user, &$sendNotification): User {
            /** @var User $lockedUser */
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);

            if ($lockedUser->suspicious_moderation_pending) {
                return $lockedUser;
            }

            $lockedUser->suspicious_violations = (int) $lockedUser->suspicious_violations + 1;
            if ($lockedUser->suspicious_violations >= 3) {
                $lockedUser->suspicious_moderation_pending = true;
                $lockedUser->suspicious_moderation_requested_at = now();
                $lockedUser->suspicious_blocked_until = null;
                $sendNotification = true;
            }

            $lockedUser->save();

            return $lockedUser;
        });

        if ($sendNotification) {
            SendTGSuspiciousAccountModeration::dispatch($user->id);
        }

        return $user;
    }

    private function payload(string $message, array $needActions, ?User $user = null, ?int $retryAfter = null): array
    {
        return [
            'success' => false,
            'error' => $message,
            'message' => $message,
            'need_actions' => $needActions,
            'shouldShowEmailPrompt' => isset($needActions['need_email'])
                || isset($needActions['need_email_verify']),
            'shouldShowPhonePrompt' => isset($needActions['need_phone'])
                || isset($needActions['need_phone_verify']),
            'suspicious_blocked_until' => $user?->suspicious_blocked_until?->toIso8601String(),
            'suspicious_retry_after' => $retryAfter,
            'retry_after' => $retryAfter,
        ];
    }
}
