<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Likes;
use App\Models\Story;
use App\Services\ApiAccountInfoService;
use App\Services\Contests\ContestListService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeelsEngagementCompatibilityController extends Controller
{
    public function saveChallenge(Request $request, int $id): JsonResponse
    {
        Challenge::query()->findOrFail($id);
        $user = $request->user();
        $meta = is_array($user->meta_data) ? $user->meta_data : [];
        $saved = collect($meta['saved_challenge_ids'] ?? [])->map(fn ($value) => (int) $value)->values();

        if ($saved->contains($id)) {
            $saved = $saved->reject(fn ($value) => (int) $value === $id)->values();
            $isSaved = false;
        } else {
            $saved->push($id);
            $saved = $saved->unique()->values();
            $isSaved = true;
        }

        $meta['saved_challenge_ids'] = $saved->all();
        $user->forceFill(['meta_data' => $meta])->save();

        return response()->json([
            'success' => true,
            'data' => ['saved' => $isSaved, 'is_saved' => $isSaved],
        ]);
    }

    public function voteChallengeResponse(Request $request, int $id): JsonResponse
    {
        $story = Story::withoutGlobalScopes()->findOrFail($id);
        if (!$story->challenge_id) {
            return response()->json(['success' => false, 'message' => 'Ответ челленджа не найден'], 404);
        }
        if ($story->challenge && $story->challenge->frozen) {
            return response()->json(['success' => false, 'message' => 'Челлендж заморожен'], 409);
        }

        return $this->ensureStoryVote($request, $story);
    }

    public function battles(Request $request): JsonResponse
    {
        $paginator = app(ContestListService::class)->battles($request);
        $rows = collect($paginator->items())->map(function (Battle $battle) use ($request): array {
            return $this->formatBattle($battle, $request);
        })->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'meta' => [
                'total' => $paginator->total(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'next_page' => $paginator->hasMorePages() ? $paginator->currentPage() + 1 : null,
            ],
        ]);
    }

    public function voteBattle(Request $request, int $id): JsonResponse
    {
        $validator = validator($request->all(), [
            'side_id' => ['nullable', 'integer'],
            'response_id' => ['nullable', 'integer'],
        ]);
        if ($validator->fails()) {
            return response()->json(['success' => false, 'errors' => $validator->errors()], 422);
        }

        $responseId = (int) ($request->input('response_id') ?: $request->input('side_id'));
        if ($responseId <= 0) {
            return response()->json([
                'success' => false,
                'errors' => ['response_id' => ['Выберите участника баттла']],
            ], 422);
        }

        $battle = Battle::query()->findOrFail($id);
        if ($battle->finished || $battle->frozen || !$battle->active || $battle->declined) {
            return response()->json(['success' => false, 'message' => 'Голосование недоступно'], 409);
        }

        $stories = $this->battleStories($battle);
        $story = $stories->firstWhere('id', $responseId);
        if (!$story) {
            return response()->json(['success' => false, 'message' => 'Участник не относится к этому баттлу'], 422);
        }

        $userId = (int) $request->user()->id;
        $storyIds = $stories->pluck('id')->map(fn ($value) => (int) $value)->all();
        $existingSelected = Likes::query()
            ->where('user_id', $userId)
            ->where('story_id', $responseId)
            ->exists();

        Likes::query()
            ->where('user_id', $userId)
            ->whereIn('story_id', $storyIds)
            ->where('story_id', '!=', $responseId)
            ->delete();

        if (!$existingSelected) {
            $ipDuplicate = Likes::query()
                ->where('story_id', $responseId)
                ->where('ip_address', $request->ip())
                ->where('user_id', '!=', $userId)
                ->exists();
            if ($ipDuplicate) {
                return response()->json(['success' => false, 'message' => 'Голос уже учтён с вашего IP-адреса'], 409);
            }

            Likes::create([
                'user_id' => $userId,
                'campaign_id' => 0,
                'story_id' => $responseId,
                'ip_address' => $request->ip(),
            ]);
        }

        $battle->refresh();
        return response()->json([
            'success' => true,
            'data' => $this->formatBattle($battle, $request),
        ]);
    }

    private function ensureStoryVote(Request $request, Story $story): JsonResponse
    {
        $userId = (int) $request->user()->id;
        $existing = Likes::query()
            ->where('user_id', $userId)
            ->where('story_id', $story->id)
            ->first();

        if (!$existing) {
            $ipDuplicate = Likes::query()
                ->where('story_id', $story->id)
                ->where('ip_address', $request->ip())
                ->where('user_id', '!=', $userId)
                ->exists();
            if ($ipDuplicate) {
                return response()->json(['success' => false, 'message' => 'Голос уже учтён с вашего IP-адреса'], 409);
            }

            Likes::create([
                'user_id' => $userId,
                'campaign_id' => 0,
                'story_id' => $story->id,
                'ip_address' => $request->ip(),
            ]);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'response_id' => $story->id,
                'voted' => true,
                'votes' => Likes::query()->where('story_id', $story->id)->count(),
            ],
        ]);
    }

    private function battleStories(Battle $battle)
    {
        return $battle->stories()
            ->where('active', true)
            ->where('declined', false)
            ->with(['user', 'media'])
            ->withCount('likes')
            ->orderByDesc('is_main_story')
            ->orderBy('created_at')
            ->limit(2)
            ->get();
    }

    private function formatBattle(Battle $battle, Request $request): array
    {
        $stories = $this->battleStories($battle);
        $totalVotes = (int) $stories->sum('likes_count');
        $userId = $request->user()?->id;
        $votedSideId = null;
        if ($userId && $stories->isNotEmpty()) {
            $votedSideId = Likes::query()
                ->where('user_id', $userId)
                ->whereIn('story_id', $stories->pluck('id'))
                ->value('story_id');
        }

        $sides = $stories->map(function (Story $story) use ($totalVotes): array {
            $votes = (int) ($story->likes_count ?? 0);
            $user = app(ApiAccountInfoService::class)->build((int) $story->user_id, true)
                ?: optional($story->user)->toArray();

            return [
                'id' => $story->id,
                'response_id' => $story->id,
                'user' => $user,
                'author' => $user,
                'votes' => $votes,
                'votes_count' => $votes,
                'percent' => $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 50,
                'vote_percent' => $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 50,
                'media_url' => $story->video_preview ?? $story->path ?? null,
                'video_url' => $story->video_preview ?? $story->path ?? null,
            ];
        })->values();

        if ($sides->count() === 1) {
            $sides[0]['percent'] = $totalVotes > 0 ? 100 : 50;
            $sides[0]['vote_percent'] = $sides[0]['percent'];
        }

        return [
            'id' => $battle->id,
            'title' => $battle->title,
            'round' => $battle->round ?? null,
            'ends_in' => $battle->end_days ?? null,
            'status' => $battle->finished ? 'finished' : 'active',
            'total_votes' => $totalVotes,
            'votes_count' => $totalVotes,
            'sides' => $sides,
            'voted_side_id' => $votedSideId,
            'user_vote' => $votedSideId,
        ];
    }
}
