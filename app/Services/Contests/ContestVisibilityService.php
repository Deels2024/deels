<?php

declare(strict_types=1);

namespace App\Services\Contests;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Story;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContestVisibilityService
{
    public const ALL = 'all';
    public const FRIENDS = 'friends';
    public const PARTICIPANTS = 'participants';

    public function canView(Model $contest, ?User $viewer): bool
    {
        $visibility = $contest->visibility ?: self::ALL;
        if ($visibility === self::ALL) {
            return true;
        }

        if (!$viewer) {
            return false;
        }

        if ($viewer->is_admin() || $this->isParticipantOrInvited($contest, (int) $viewer->id)) {
            return true;
        }

        return $visibility === self::FRIENDS
            && $this->hasParticipantFriend($contest, (int) $viewer->id);
    }

    public function applyToContests(Builder|Relation $query, string $table, ?User $viewer): Builder|Relation
    {
        if ($viewer && $viewer->is_admin()) {
            return $query;
        }

        return $query->where(function (Builder $visibilityQuery) use ($table, $viewer): void {
            $visibilityQuery
                ->whereNull($table . '.visibility')
                ->orWhere($table . '.visibility', self::ALL);

            if (!$viewer) {
                return;
            }

            $viewerId = (int) $viewer->id;
            $friendIds = $this->friendIds($viewerId);

            $visibilityQuery->orWhere($table . '.user_id', $viewerId);
            if (DB::connection()->getDriverName() === 'sqlite') {
                $visibilityQuery->orWhereRaw(
                    "EXISTS (SELECT 1 FROM json_each(COALESCE({$table}.invite_user_ids, '[]')) WHERE CAST(json_each.value AS INTEGER) = ?)",
                    [$viewerId]
                );
            } else {
                $visibilityQuery->orWhereJsonContains($table . '.invite_user_ids', $viewerId);
            }
            $visibilityQuery->orWhereExists(function ($stories) use ($table, $viewerId): void {
                    $stories->selectRaw('1')
                        ->from('stories')
                        ->whereColumn('stories.' . $this->foreignKey($table), $table . '.id')
                        ->where('stories.user_id', $viewerId)
                        ->whereNull('stories.withdrawn_at')
                        ->where(function ($query): void {
                            $query->where('stories.is_main_story', false)
                                ->orWhereNull('stories.is_main_story');
                        });
                });

            if (Schema::hasTable('contest_participations')) {
                $visibilityQuery->orWhereExists(function ($participations) use ($table, $viewerId): void {
                    $participations->selectRaw('1')
                        ->from('contest_participations')
                        ->whereColumn('contest_participations.contest_id', $table . '.id')
                        ->where('contest_participations.contest_type', $this->contestType($table))
                        ->where('contest_participations.user_id', $viewerId)
                        ->where('contest_participations.status', 'active');
                });
            }

            if ($table === 'battles') {
                $visibilityQuery->orWhere($table . '.called_user_id', $viewerId);
            }

            if (!$friendIds) {
                return;
            }

            $visibilityQuery->orWhere(function (Builder $friendsQuery) use ($table, $friendIds): void {
                $friendsQuery
                    ->where($table . '.visibility', self::FRIENDS)
                    ->where(function (Builder $participantQuery) use ($table, $friendIds): void {
                        $participantQuery
                            ->whereIn($table . '.user_id', $friendIds)
                            ->orWhereExists(function ($stories) use ($table, $friendIds): void {
                                $stories->selectRaw('1')
                                    ->from('stories')
                                    ->whereColumn('stories.' . $this->foreignKey($table), $table . '.id')
                                    ->whereIn('stories.user_id', $friendIds)
                                    ->whereNull('stories.withdrawn_at')
                                    ->where(function ($query): void {
                                        $query->where('stories.is_main_story', false)
                                            ->orWhereNull('stories.is_main_story');
                                    });
                            });

                        if ($table === 'battles') {
                            $participantQuery->orWhereIn($table . '.called_user_id', $friendIds);
                        }

                        if (Schema::hasTable('contest_participations')) {
                            $participantQuery->orWhereExists(function ($participations) use ($table, $friendIds): void {
                                $participations->selectRaw('1')
                                    ->from('contest_participations')
                                    ->whereColumn('contest_participations.contest_id', $table . '.id')
                                    ->where('contest_participations.contest_type', $this->contestType($table))
                                    ->whereIn('contest_participations.user_id', $friendIds)
                                    ->where('contest_participations.status', 'active');
                            });
                        }
                    });
            });
        });
    }

    public function applyToStories(Builder $query, ?User $viewer): Builder
    {
        return $query->where(function (Builder $storyQuery) use ($viewer): void {
            $storyQuery->where(function (Builder $plainStory): void {
                $plainStory->whereNull('stories.challenge_id')
                    ->whereNull('stories.battle_id');
            })->orWhere(function (Builder $challengeStory) use ($viewer): void {
                $challengeStory->whereNotNull('stories.challenge_id')
                    ->whereHas('challenge', fn (Builder $query) => $this->applyToContests($query, 'challenges', $viewer));
            })->orWhere(function (Builder $battleStory) use ($viewer): void {
                $battleStory->whereNotNull('stories.battle_id')
                    ->whereHas('battle', fn (Builder $query) => $this->applyToContests($query, 'battles', $viewer));
            });
        });
    }

    private function isParticipantOrInvited(Model $contest, int $viewerId): bool
    {
        if ((int) $contest->user_id === $viewerId
            || in_array($viewerId, array_map('intval', (array) $contest->invite_user_ids), true)
            || ($contest instanceof Battle && (int) $contest->called_user_id === $viewerId)
            || $this->hasActiveParticipation($contest, $viewerId)
        ) {
            return true;
        }

        return $this->participantQuery($contest)->where('user_id', $viewerId)->exists();
    }

    private function hasParticipantFriend(Model $contest, int $viewerId): bool
    {
        $friendIds = $this->friendIds($viewerId);
        if (!$friendIds) {
            return false;
        }

        if (in_array((int) $contest->user_id, $friendIds, true)
            || ($contest instanceof Battle && in_array((int) $contest->called_user_id, $friendIds, true))
        ) {
            return true;
        }

        if (Schema::hasTable('contest_participations')
            && DB::table('contest_participations')
                ->where('contest_type', $contest instanceof Battle ? 'battle' : 'challenge')
                ->where('contest_id', $contest->id)
                ->whereIn('user_id', $friendIds)
                ->where('status', 'active')
                ->exists()
        ) {
            return true;
        }

        return $this->participantQuery($contest)->whereIn('user_id', $friendIds)->exists();
    }

    private function participantQuery(Model $contest): Builder
    {
        $foreignKey = $contest instanceof Battle ? 'battle_id' : 'challenge_id';

        return Story::withoutGlobalScopes()
            ->where($foreignKey, $contest->id)
            ->whereNull('withdrawn_at')
            ->notMainStory();
    }

    private function hasActiveParticipation(Model $contest, int $viewerId): bool
    {
        if (!Schema::hasTable('contest_participations')) {
            return false;
        }

        $type = $contest instanceof Battle ? 'battle' : 'challenge';

        return DB::table('contest_participations')
            ->where([
                'contest_type' => $type,
                'contest_id' => $contest->id,
                'user_id' => $viewerId,
                'status' => 'active',
            ])
            ->exists();
    }

    private function friendIds(int $viewerId): array
    {
        return DB::table('followables as outgoing')
            ->join('followables as incoming', function ($join): void {
                $join->on('incoming.user_id', '=', 'outgoing.followable_id')
                    ->on('incoming.followable_id', '=', 'outgoing.user_id');
            })
            ->where('outgoing.user_id', $viewerId)
            ->where('outgoing.followable_type', User::class)
            ->where('incoming.followable_type', User::class)
            ->whereNotNull('outgoing.accepted_at')
            ->whereNotNull('incoming.accepted_at')
            ->pluck('outgoing.followable_id')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    private function foreignKey(string $table): string
    {
        return $table === 'battles' ? 'battle_id' : 'challenge_id';
    }

    private function contestType(string $table): string
    {
        return $table === 'battles' ? 'battle' : 'challenge';
    }
}
