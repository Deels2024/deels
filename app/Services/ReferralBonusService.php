<?php

declare(strict_types=1);

namespace App\Services;

use App\Jobs\User\UpdateUsersBalance;
use App\Models\Campaign;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;

class ReferralBonusService
{
    private const BONUS_AMOUNT = 500;
    private const BONUS_META_KEY = 'referral_registration_bonus';
    private const BONUS_PERIOD_DAYS = 30;

    public function awardForRegistration(User $invitedUser): void
    {
        $this->award($invitedUser, 'registration');
    }

    public function awardForFirstDonate(User $invitedUser): void
    {
        if (!$this->isFirstDonate($invitedUser)) {
            return;
        }

        $this->award($invitedUser, 'first_donate');
    }

    private function award(User $invitedUser, string $reason): void
    {
        if (!$invitedUser->is_activated || !$invitedUser->invite_referral_code || $invitedUser->created_at->lt(Carbon::now()->subDays(self::BONUS_PERIOD_DAYS))) {
            return;
        }

        $invitor = User::query()
            ->where('referral_code', $invitedUser->invite_referral_code)
            ->first();

        if (!$invitor || $invitor->id === $invitedUser->id || $this->alreadyPaid($invitor, $invitedUser)) {
            return;
        }

        UpdateUsersBalance::dispatchSync($invitor->id, self::BONUS_AMOUNT / 50, [], [], 'Бонус за приглашенного пользователя');
        Campaign::healthUp(1, $invitor->id);
        $this->markAsPaid($invitedUser, $invitor, $reason);
    }

    private function alreadyPaid(User $invitor, User $invitedUser): bool
    {
        $metaData = $invitedUser->meta_data ?? [];
        if (($metaData['referral_bonus']['paid'] ?? false) === true) {
            return true;
        }

        return Transaction::query()
            ->where('payable_type', User::class)
            ->where('payable_id', $invitor->id)
            ->where('meta', 'like', '%"' . self::BONUS_META_KEY . '"%')
            ->where('meta', 'like', '%"referral_id":' . $invitedUser->id . '%')
            ->exists();
    }

    private function markAsPaid(User $invitedUser, User $invitor, string $reason): void
    {
        $metaData = $invitedUser->meta_data ?? [];
        $metaData['referral_bonus'] = [
            'paid' => true,
            'invitor_id' => $invitor->id,
            'reason' => $reason,
            'paid_at' => now()->toDateTimeString(),
        ];

        $invitedUser->forceFill(['meta_data' => $metaData])->save();
    }

    private function isFirstDonate(User $user): bool
    {
        return Transaction::query()
            ->where('payable_type', User::class)
            ->where('payable_id', $user->id)
            ->where('type', Transaction::TYPE_WITHDRAW)
            ->where(function ($query): void {
                $query
                    ->where('meta', 'like', '%"donate":"campaign"%')
                    ->orWhere('meta', 'like', '%"donate":"story"%')
                    ->orWhere('meta', 'like', '%"donate":"stream"%');
            })
            ->where('meta', 'not like', '%хранение%')
            ->count() === 1;
    }
}
