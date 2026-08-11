<?php

declare(strict_types=1);

namespace App\Services\Contests;

use App\Helpers\AppHelper;
use App\Models\Challenge;
use App\Models\Story;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ChallengeWinnerSelectionService
{
    public function finishByLikes(Challenge $challenge): array
    {
        $stories = $this->eligibleStories($challenge);
        $bestStory = $stories->first();

        if (!$bestStory || (int) $bestStory->likes_count <= 0) {
            $this->refundChallenge($challenge);

            return [
                'stories' => $stories,
                'winner_story_ids' => [],
                'prize' => (int) $challenge->amount,
                'has_winners' => false,
            ];
        }

        $winnerStories = $stories->filter(static function (Story $story) use ($bestStory): bool {
            return (int) $story->likes_count === (int) $bestStory->likes_count;
        })->values();

        $prize = $this->awardWinnerStories($challenge, $winnerStories);

        return [
            'stories' => $stories,
            'winner_story_ids' => $winnerStories->pluck('id')->all(),
            'prize' => $prize,
            'has_winners' => $winnerStories->isNotEmpty(),
        ];
    }

    public function finishByCreator(Challenge $challenge, array $userIds, ?int $decidedByUserId = null): array
    {
        $stories = $this->eligibleStories($challenge);
        $winnerUserIds = collect($userIds)
            ->map(static fn ($userId): int => (int) $userId)
            ->intersect($this->eligibleWinnerUserIds($challenge))
            ->unique()
            ->values();
        $winnerStories = $stories
            ->whereIn('user_id', $winnerUserIds)
            ->unique('user_id')
            ->values();

        $prize = $this->awardWinnerUserIds($challenge, $winnerUserIds);

        $challenge->winner_selection_status = 'selected';
        $challenge->winner_selected_at = now();
        $challenge->winner_decided_by_user_id = $decidedByUserId;
        $challenge->saveQuietly();

        return [
            'stories' => $stories,
            'winner_story_ids' => $winnerStories->pluck('id')->all(),
            'winner_user_ids' => $winnerUserIds->all(),
            'prize' => $prize,
            'has_winners' => $winnerUserIds->isNotEmpty(),
        ];
    }

    public function finishPendingByFallback(Challenge $challenge): array
    {
        $result = $this->finishByLikes($challenge);

        $challenge->winner_selection_status = 'auto';
        $challenge->winner_selected_at = now();
        $challenge->winner_decided_by_user_id = null;
        $challenge->saveQuietly();

        return $result;
    }

    public function eligibleWinnerUserIds(Challenge $challenge): array
    {
        if (Schema::hasTable('contest_participations')) {
            return DB::table('contest_participations')
                ->where([
                    'contest_type' => 'challenge',
                    'contest_id' => $challenge->id,
                    'status' => 'active',
                ])
                ->pluck('user_id')
                ->map(static fn ($userId): int => (int) $userId)
                ->unique()
                ->values()
                ->all();
        }

        return $this->eligibleStories($challenge)
            ->pluck('user_id')
            ->map(static fn ($userId): int => (int) $userId)
            ->unique()
            ->values()
            ->all();
    }

    private function eligibleStories(Challenge $challenge): Collection
    {
        return Story::withCount('likes')
            ->active()
            ->notUseful()
            ->orderBy('likes_count', 'desc')
            ->where('challenge_id', $challenge->id)
            ->where(function ($query): void {
                $query->where('is_main_story', false)
                    ->orWhereNull('is_main_story');
            })
            ->where('frozen', false)
            ->where('banned', false)
            ->get();
    }

    private function awardWinnerStories(Challenge $challenge, Collection $winnerStories): int
    {
        return $this->awardWinnerUserIds($challenge, $winnerStories->pluck('user_id'));
    }

    private function awardWinnerUserIds(Challenge $challenge, Collection $winnerUserIds): int
    {
        $amount = (int) $challenge->amount;

        $winnerUserIds = $winnerUserIds
            ->map(static fn ($userId): int => (int) $userId)
            ->unique()
            ->values();

        if ($winnerUserIds->isEmpty()) {
            $this->refundChallenge($challenge);

            return $amount;
        }

        $prize = (int) ceil($amount / max(1, $winnerUserIds->count()));
        $currentWinners = $challenge->winners()->pluck('user_id')->map(static fn ($userId): int => (int) $userId)->all();
        $telegram = new AppHelper();

        foreach ($winnerUserIds as $winnerUserId) {
            if (in_array($winnerUserId, $currentWinners, true)) {
                continue;
            }

            $winner = User::find($winnerUserId);
            if (!$winner) {
                continue;
            }

            if ($prize > 0) {
                $winner->deposit($prize, ['get' => 'coins', 'description' => 'Победа в челлендже "' . $challenge->title . '"']);
            }
            $challenge->winners()->syncWithoutDetaching([$winner->id]);
            $telegram->telegram_message('Challenge ID' . $challenge->id . ' winner: ' . $winner->fullname . ' (ID ' . $winner->id . ') / Prize: ' . $prize . ' coins');
        }

        return $prize;
    }

    private function refundChallenge(Challenge $challenge): void
    {
        $amount = (int) $challenge->amount;
        $paymentsWallet = $challenge->user->getWallet('payments');
        $balance = (int) ($paymentsWallet->balance ?? 0);

        $paymentsWallet->deposit($amount, [
            'get' => 'coins',
            'balance_before' => $balance,
            'description' => 'Возврат за челлендж "' . $challenge->title . '"',
        ]);

        if($challenge->cost && $challenge->cost > 0) {
            foreach ($challenge->stories as $participantStory) {
                $participantStoryPaymentsWallet = $participantStory->user->getWallet('payments');
                $participantStoryBalance = (int) ($participantStoryPaymentsWallet->balance ?? 0);
                $participantStoryPaymentsWallet->deposit((int) $challenge->cost, [
                    'get' => 'coins',
                    'balance_before' => $participantStoryBalance,
                    'description' => 'Возврат за участие в челлендже "' . $challenge->title . '"',
                ]);
            }
        }

        (new AppHelper())->telegram_message('Возврат за челлендж ID'.$challenge->id.' "' . $challenge->title . '"');
    }
}
