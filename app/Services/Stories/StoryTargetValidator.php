<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Models\Battle;
use App\Models\Campaign;
use App\Models\Challenge;
use App\Services\Contests\ContestParticipationService;
use App\Services\Contests\ContestVisibilityService;

class StoryTargetValidator
{
    public function __construct(
        private ContestParticipationService $participation,
        private ?ContestVisibilityService $visibility = null
    )
    {
    }

    public function validateChallenge($challengeId, $userId): bool
    {
        $challenge = Challenge::find($challengeId);

        $user = $userId ? \App\Models\User::find($userId) : null;
        if (!$challenge || !$challenge->active || $challenge->finished || $challenge->declined
            || $userId == $challenge->user_id || !$this->visibility()->canView($challenge, $user)) {
            return false;
        }

        $state = $this->participation->state($challenge, 'challenge', (int) $userId);

        return $state['action'] === 'join'
            || ($state['participating'] && !$state['hasResult']);
    }

    public function validateBattle($battleId, $userId): bool
    {
        $battle = Battle::find($battleId);

        $user = $userId ? \App\Models\User::find($userId) : null;
        if (!$battle || !$battle->active || $battle->finished || $battle->declined
            || $userId == $battle->user_id || !$this->visibility()->canView($battle, $user)) {
            return false;
        }

        $state = $this->participation->state($battle, 'battle', (int) $userId);

        return in_array($state['action'], ['join', 'accept'], true)
            || ($state['participating'] && !$state['hasResult']);
    }

    public function validateUsefulChallenge($challengeId, $userId): bool
    {
        return (bool) $challengeId
            && Challenge::whereKey($challengeId)->where('user_id', $userId)->exists();
    }

    public function validateUsefulBattle($battleId, $userId): bool
    {
        return (bool) $battleId
            && Battle::whereKey($battleId)->where('user_id', $userId)->exists();
    }

    public function validateCampaign($campaignId, $userId): bool
    {
        return Campaign::where('id', $campaignId)
            ->where('user_id', $userId)
            ->exists();
    }

    private function visibility(): ContestVisibilityService
    {
        return $this->visibility ??= app(ContestVisibilityService::class);
    }
}
