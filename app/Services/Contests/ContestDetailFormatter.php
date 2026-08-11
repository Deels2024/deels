<?php

declare(strict_types=1);

namespace App\Services\Contests;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContestDetailFormatter
{
    public function __construct(private readonly ContestAccountInfoCache $accountInfo)
    {
    }

    public function formatBattle(Battle $battle, Request $request): array
    {
        return $this->format($battle, $request, 'battle', 'battle_id', 'battles.get.video', true);
    }

    public function formatChallenge(Challenge $challenge, Request $request): array
    {
        return $this->format($challenge, $request, 'challenge', 'challenge_id', 'challenges.get.video', false);
    }

    private function format($contest, Request $request, string $type, string $idKey, string $videoRoute, bool $winnersWithoutGlobalScopes): array
    {
        $user = $this->resolveUser($request);
        $userMissing = (auth()->id() === null && $request->input('user_id') && !$user);
        $participant = $user ? $contest->participant($user->id) : null;
        $blocked = $user ? $user->blockedBy($contest->user_id) : false;
        $stories = $contest->stories()->orderByDesc('is_main_story')->latest()->paginate(12);
        $storyForeignKey = $type . '_id';

        $dataStories = [];
        foreach ($stories as $story) {
            $story->user = $this->accountInfo->build((int) $story->user_id, true);
            $story->likes = null;
            $story->comments = null;
            $dataStories[] = $story;
        }

        $winnerStories = [];
        foreach ($contest->winners as $winner) {
            $winnerStoryQuery = $winnersWithoutGlobalScopes ? Story::withoutGlobalScopes() : Story::query();
            $winnerStory = $winnerStoryQuery
                ->where('user_id', $winner->id)
                ->where($storyForeignKey, $contest->id)
                ->where(function ($query): void {
                    $query->where('is_main_story', false)
                        ->orWhereNull('is_main_story');
                })
                ->first();

            if ($winnerStory) {
                $winnerStory->likes = null;
                $winnerStory->comments = null;
                $winnerStory->user = $this->accountInfo->build((int) $winnerStory->user_id, true);
                $winnerStories[] = $winnerStory;
            }
        }

        $mainStory = $contest->getMainStory()->first();
        $mainStoryType = $mainStory ? $mainStory->type : $contest->type;
        $mainStoryPath = $mainStory
            ? $mainStory->path
            : ($contest->media ? route($videoRoute, $contest->id) : $contest->getFile());
        $usefulStories = $contest->usefulStories()
            ->where('stories.declined', false)
            ->whereNull('stories.blocked_at')
            ->whereNull('stories.withdrawn_at')
            ->where('stories.broken', false)
            ->latest()
            ->get()
            ->map(function (Story $story) {
            $story->user = $this->accountInfo->build((int) $story->user_id, true);
            $story->likes = null;
            $story->comments = null;

            return $story;
        })->values();
        $participantUserIds = Schema::hasTable('contest_participations')
            ? DB::table('contest_participations')
                ->where('contest_type', $type)
                ->where('contest_id', $contest->id)
                ->where('status', 'active')
                ->pluck('user_id')
                ->map(fn ($id) => (int) $id)
                ->values()
            : collect();

        return [
            'blocked' => $blocked,
            'user_missing' => $userMissing,
            'data' => [
                $idKey => $contest->id,
                'title' => $contest->title,
                'views' => 0,
                'amount' => $contest->amount,
                'description' => $contest->description,
                'type' => $mainStoryType,
                'declined' => $contest->declined,
                'start' => $contest->start,
                'finish' => $contest->finish,
                'finished' => $contest->finished,
                'finished_at' => $contest->finished_at,
                'completion_status' => $contest->completion_status,
                'blocked_at' => $contest->blocked_at,
                'min_participants' => $contest->min_participants,
                'participants_count' => $contest->participants_count,
                'days' => $contest->days,
                'days_left' => $contest->daysLeft(),
                'date_from' => $contest->date_from,
                'date_to' => $contest->date_to,
                'visibility' => $contest->visibility,
                'rhythm' => $contest->rhythm,
                'checkin' => $contest->checkin,
                'reward_amount' => $contest->reward_amount,
                'winner_selection' => $contest->winner_selection,
                'winner_selection_status' => $contest->winner_selection_status ?? null,
                'winner_selection_deadline' => $contest->winner_selection_deadline ?? null,
                'winner_selected_at' => $contest->winner_selected_at ?? null,
                'winner_decided_by_user_id' => $contest->winner_decided_by_user_id ?? null,
                'invite_user_ids' => $contest->invite_user_ids ?? [],
                'called_user_id' => $type === 'battle' ? $contest->called_user_id : null,
                'by_views' => $contest->by_views,
                'by_likes' => $contest->by_likes,
                'by_comments' => $contest->by_comments,
                'comments_count' => 0,
                'path' => $mainStoryPath,
                'likes_count' => 0,
                'thumbnail' => $contest->thumbnail,
                'video_preview' => $contest->video_preview,
                'user' => $this->accountInfo->build((int) $contest->user_id, true),
                'participant' => $participant,
                'participants' => $contest->participants ?? 0,
                'participant_user_ids' => $participantUserIds,
                'winners' => $winnerStories,
                'main_story' => $mainStory,
                'useful_stories' => $usefulStories,
                'stories' => $dataStories,
                'current_page' => $stories->currentPage(),
                'total_pages' => $stories->lastPage(),
            ],
        ];
    }

    private function resolveUser(Request $request): ?User
    {
        $userId = auth()->id() ?? $request->input('user_id');
        if (!$userId) {
            return null;
        }

        return User::find($userId);
    }
}
