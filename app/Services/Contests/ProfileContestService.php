<?php

declare(strict_types=1);

namespace App\Services\Contests;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Collection;

class ProfileContestService
{
    public function __construct(private ContestVisibilityService $visibility) {}

    public function forProfile(User $profileUser, ?User $viewer): array
    {
        $challengeQuery = $this->challengeQuery((int) $profileUser->id);
        $battleQuery = $this->battleQuery((int) $profileUser->id);

        $totalCount = (clone $challengeQuery)->count() + (clone $battleQuery)->count();

        $this->visibility->applyToContests($challengeQuery, 'challenges', $viewer);
        $this->visibility->applyToContests($battleQuery, 'battles', $viewer);

        $challenges = $challengeQuery->get()->each(function (Challenge $challenge): void {
            $challenge->setAttribute('profile_contest_type', 'challenge');
        });
        $battles = $battleQuery->get()->each(function (Battle $battle): void {
            $battle->setAttribute('profile_contest_type', 'battle');
        });

        /** @var Collection<int, Challenge|Battle> $contests */
        $contests = $challenges
            ->concat($battles)
            ->sortByDesc('created_at')
            ->values();

        return [
            'contests' => $contests,
            'hidden_count' => max(0, $totalCount - $contests->count()),
        ];
    }

    private function challengeQuery(int $profileUserId): Builder
    {
        return Challenge::query()
            ->with(['user', 'media', 'getMainStory', 'winners'])
            ->where(function (Builder $query) use ($profileUserId): void {
                $query->where('challenges.user_id', $profileUserId)
                    ->orWhereExists(function ($stories) use ($profileUserId): void {
                        $this->participantStories($stories, 'challenge_id', 'challenges', $profileUserId);
                    });
            });
    }

    private function battleQuery(int $profileUserId): Builder
    {
        return Battle::query()
            ->with(['user', 'media', 'getMainStory', 'winners'])
            ->where(function (Builder $query) use ($profileUserId): void {
                $query->where('battles.user_id', $profileUserId)
                    ->orWhere('battles.called_user_id', $profileUserId)
                    ->orWhereExists(function ($stories) use ($profileUserId): void {
                        $this->participantStories($stories, 'battle_id', 'battles', $profileUserId);
                    });
            });
    }

    private function participantStories(
        QueryBuilder $stories,
        string $foreignKey,
        string $contestTable,
        int $profileUserId
    ): void {
        $stories->selectRaw('1')
            ->from('stories')
            ->whereColumn('stories.'.$foreignKey, $contestTable.'.id')
            ->where('stories.user_id', $profileUserId)
            ->whereNull('stories.withdrawn_at')
            ->where(function ($query): void {
                $query->where('stories.is_main_story', false)
                    ->orWhereNull('stories.is_main_story');
            });
    }
}
