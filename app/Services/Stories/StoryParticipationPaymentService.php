<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\User;

class StoryParticipationPaymentService
{
    private const INSUFFICIENT_FUNDS_ERROR = 'Недостаточно дилсов для оплаты участия. Пополните баланс!';

    public function payForChallengeIfNeeded($challengeId, $userId, bool $hasExistingStory): ?string
    {
        if ($hasExistingStory) {
            return null;
        }

        $challenge = Challenge::find($challengeId);
        if (!$challenge || !$challenge->cost || $challenge->cost <= 0) {
            return null;
        }

        return $this->withdraw(
            $userId,
            (int) $challenge->cost,
            ['create' => 'challenge', 'description' => 'Оплата за участие в челлендже: ' . $challenge->title]
        );
    }

    public function payForBattleIfNeeded($battleId, $userId, bool $hasExistingStory): ?string
    {
        if ($hasExistingStory || !$battleId) {
            return null;
        }

        $battle = Battle::find($battleId);
        if (!$battle || !$battle->cost || $battle->cost <= 0) {
            return null;
        }

        return $this->withdraw(
            $userId,
            (int) $battle->cost,
            ['create' => 'battle', 'description' => 'Оплата за участие в батле: ' . $battle->title]
        );
    }

    private function withdraw($userId, int $amount, array $meta): ?string
    {
        $user = User::find($userId);
        $paymentsWallet = $user->getWallet('payments');

        try {
            $paymentsWallet->withdraw($amount, $meta);
        } catch (\Throwable $e) {
            return self::INSUFFICIENT_FUNDS_ERROR;
        }

        return null;
    }
}
