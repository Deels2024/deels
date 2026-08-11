<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Models\Story;

class StoryReplacementService
{
    public function hasChallengeStory($challengeId, $userId): bool
    {
        return $this->hasStoryByColumn('challenge_id', $challengeId, $userId);
    }

    public function hasBattleStory($battleId, $userId): bool
    {
        if (!$battleId) {
            return false;
        }

        return $this->baseQueryWithoutGlobalScopes('battle_id', $battleId, $userId)->count() > 0;
    }

    public function deleteChallengeStory($challengeId, $userId): bool
    {
        if ($challengeId) {
            $this->baseQuery('challenge_id', $challengeId, $userId)->delete();
        }

        return false;
    }

    public function deleteBattleStory($battleId, $userId): bool
    {
        if ($battleId) {
            $this->baseQueryWithoutGlobalScopes('battle_id', $battleId, $userId)->delete();
        }

        return false;
    }

    private function hasStoryByColumn(string $column, $id, $userId): bool
    {
        if (!$id) {
            return false;
        }

        return $this->baseQuery($column, $id, $userId)->count() > 0;
    }

    private function baseQuery(string $column, $id, $userId)
    {
        return Story::where($column, $id)
            ->where('user_id', $userId)
            ->where(function ($query): void {
                $query->where('is_main_story', false)
                    ->orWhereNull('is_main_story');
            });
    }

    private function baseQueryWithoutGlobalScopes(string $column, $id, $userId)
    {
        return Story::withoutGlobalScopes()
            ->where($column, $id)
            ->where('user_id', $userId)
            ->where(function ($query): void {
                $query->where('is_main_story', false)
                    ->orWhereNull('is_main_story');
            });
    }
}
