<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\ContestInvitationController;
use App\Http\Controllers\Controller;
use App\Models\Battle;
use App\Models\Likes;
use App\Models\Story;
use App\Services\ApiAccountInfoService;
use App\Services\Contests\ContestParticipationService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class DeelsBattleCompatibilityController extends Controller
{
    public function show(Request $request, int $id): JsonResponse
    {
        $battle = Battle::query()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatBattle($battle, $request),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $this->prepareBattleRequest($request);

        return $this->storeLegacyBattle($request);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $this->prepareBattleRequest($request, $id);

        return $this->storeLegacyBattle($request);
    }

    public function accept(Request $request, int $id, ContestParticipationService $participation): JsonResponse
    {
        $battle = Battle::query()->findOrFail($id);
        $participation->accept($battle, (int) $request->user()->id);

        return $this->stateResponse($battle->fresh(), $request, 'active', 'Вызов принят');
    }

    public function decline(Request $request, int $id, ContestParticipationService $participation): JsonResponse
    {
        $battle = Battle::query()->findOrFail($id);
        $participation->decline($battle, (int) $request->user()->id);

        return $this->stateResponse($battle->fresh(), $request, 'declined', 'Вызов отклонен');
    }

    public function leave(Request $request, int $id, ContestParticipationService $participation): JsonResponse
    {
        $battle = Battle::query()->findOrFail($id);
        $participation->leave($battle, 'battle', (int) $request->user()->id);

        return $this->stateResponse($battle->fresh(), $request, 'left', 'Участие прекращено');
    }

    public function rejoin(Request $request, int $id, ContestParticipationService $participation): JsonResponse
    {
        $battle = Battle::query()->findOrFail($id);
        $participation->rejoin($battle, 'battle', (int) $request->user()->id);

        return $this->stateResponse($battle->fresh(), $request, 'active', 'Участие возобновлено');
    }

    public function inviteUsers(Request $request, int $id): JsonResponse
    {
        return app(ContestInvitationController::class)->users($request, 'battle', $id, app(\App\Services\Contests\ContestInvitationService::class));
    }

    public function invites(Request $request, int $id): JsonResponse
    {
        return app(ContestInvitationController::class)->store(
            $request,
            'battle',
            $id,
            app(\App\Services\Contests\ContestInvitationService::class),
            app(\App\Services\Contests\ContestNotificationService::class)
        );
    }

    public function response(Request $request, int $id): JsonResponse
    {
        $validator = validator($request->all(), [
            'media' => 'required_without:media_id|file',
            'media_id' => 'required_without:media|nullable|integer',
            'caption' => 'nullable|string|max:5000',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Проверьте видеоответ',
                'errors' => $validator->errors(),
            ], 422);
        }

        Battle::query()->findOrFail($id);
        if ($request->hasFile('media')) {
            $request->files->set('file', $request->file('media'));
        }
        $request->merge([
            'battle_id' => $id,
            'description' => $request->input('caption', ''),
            'amount' => 0,
        ]);

        $legacy = app(StoryUploadController::class)->store($request);
        if (!$legacy instanceof JsonResponse) {
            return response()->json(['success' => false, 'message' => 'Не удалось сохранить ответ'], 422);
        }
        $payload = $legacy->getData(true);
        if (empty($payload['success'])) {
            return response()->json([
                'success' => false,
                'message' => $payload['error'] ?? 'Не удалось сохранить ответ',
                'error' => $payload['error'] ?? null,
                'errors' => $payload['errors'] ?? [],
            ], $legacy->getStatusCode() >= 400 ? $legacy->getStatusCode() : 422);
        }

        $storyId = (int) ($payload['story_id'] ?? $payload['id'] ?? 0);
        $story = Story::withoutGlobalScopes()->with(['user', 'media'])->withCount('likes')->find($storyId);

        return response()->json([
            'success' => true,
            'data' => $story ? $this->formatSide($story, max(1, (int) $story->likes_count)) : ['id' => $storyId],
        ], 201);
    }

    private function prepareBattleRequest(Request $request, ?int $id = null): void
    {
        $validator = validator($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'opponent_id' => 'required|integer|exists:users,id',
            'prize' => 'nullable|integer|min:0',
            'ends_at' => 'nullable|date',
            'media' => $id ? 'nullable|file' : 'required|file',
        ]);
        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
        if ((int) $request->input('opponent_id') === (int) $request->user()->id) {
            throw ValidationException::withMessages(['opponent_id' => ['Нельзя вызвать самого себя']]);
        }

        if ($request->hasFile('media')) {
            $request->files->set('mainImg', $request->file('media'));
        }

        $days = 7;
        if ($request->filled('ends_at')) {
            $end = Carbon::parse((string) $request->input('ends_at'));
            $days = max(1, Carbon::now()->startOfDay()->diffInDays($end->startOfDay(), false));
        }

        $request->merge([
            'battle_id' => $id,
            'amount' => 0,
            'reward_amount' => $request->filled('prize') ? (int) $request->input('prize') : null,
            'criteria' => ['by_likes'],
            'min_participants' => 2,
            'participants_visual' => '2',
            'days' => $days,
            'visibility' => $request->input('visibility', 'all'),
            'rhythm_visual' => 'once',
            'checkin_visual' => 'story',
            'winner_selection' => 'likes',
            'called_user_id' => (int) $request->input('opponent_id'),
            'invite_user_ids' => [(int) $request->input('opponent_id')],
            'cost' => 0,
        ]);
    }

    private function storeLegacyBattle(Request $request): JsonResponse
    {
        $legacy = app(BattleController::class)->store_web($request);
        if (!$legacy instanceof JsonResponse) {
            return response()->json(['success' => false, 'message' => 'Не удалось сохранить баттл'], 422);
        }
        $payload = $legacy->getData(true);
        if (empty($payload['success'])) {
            return response()->json([
                'success' => false,
                'message' => $payload['error'] ?? 'Проверьте параметры баттла',
                'error' => $payload['error'] ?? null,
                'errors' => $payload['errors'] ?? [],
            ], $legacy->getStatusCode() >= 400 ? $legacy->getStatusCode() : 422);
        }

        $id = (int) ($payload['battle_id'] ?? $request->input('battle_id'));
        $battle = Battle::query()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $this->formatBattle($battle, $request),
        ], $request->input('battle_id') ? 200 : 201);
    }

    private function stateResponse(Battle $battle, Request $request, string $status, string $message): JsonResponse
    {
        return response()->json([
            'success' => true,
            'status' => $status,
            'message' => $message,
            'data' => $this->formatBattle($battle, $request),
        ]);
    }

    private function battleStories(Battle $battle)
    {
        return $battle->stories()
            ->where('active', true)
            ->where('declined', false)
            ->with(['user', 'media'])
            ->withCount('likes')
            ->orderBy('created_at')
            ->limit(2)
            ->get();
    }

    private function formatBattle(Battle $battle, Request $request): array
    {
        $stories = $this->battleStories($battle);
        $totalVotes = (int) $stories->sum('likes_count');
        $votedSideId = $request->user() && $stories->isNotEmpty()
            ? Likes::query()->where('user_id', $request->user()->id)->whereIn('story_id', $stories->pluck('id'))->value('story_id')
            : null;
        $sides = $stories->map(fn (Story $story) => $this->formatSide($story, $totalVotes))->values();

        $participation = app(ContestParticipationService::class)->state($battle, 'battle', $request->user()?->id ? (int) $request->user()->id : null);

        return [
            'id' => $battle->id,
            'title' => $battle->title,
            'description' => $battle->description,
            'status' => $battle->finished ? 'finished' : ($battle->frozen ? 'frozen' : 'active'),
            'status_title' => $battle->status_title,
            'ends_at' => optional($battle->date_to)->toIso8601String() ?? optional($battle->finish)->toIso8601String(),
            'total_votes' => $totalVotes,
            'votes_count' => $totalVotes,
            'sides' => $sides,
            'voted_side_id' => $votedSideId ? (int) $votedSideId : null,
            'user_vote' => $votedSideId ? (int) $votedSideId : null,
            'opponent_id' => $battle->called_user_id ? (int) $battle->called_user_id : null,
            'participation' => $participation,
            'can_vote' => !$battle->finished && !$battle->frozen && (bool) $battle->active && !(bool) $battle->declined,
        ];
    }

    private function formatSide(Story $story, int $totalVotes): array
    {
        $votes = (int) ($story->likes_count ?? Likes::query()->where('story_id', $story->id)->count());
        $user = app(ApiAccountInfoService::class)->build((int) $story->user_id, true) ?: optional($story->user)->toArray();
        $percent = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 50.0;

        return [
            'id' => (int) $story->id,
            'response_id' => (int) $story->id,
            'user' => $user,
            'author' => $user,
            'votes' => $votes,
            'votes_count' => $votes,
            'percent' => $percent,
            'vote_percent' => $percent,
            'media_url' => $story->video_preview ?? $story->path ?? null,
            'video_url' => $story->video_preview ?? $story->path ?? null,
            'thumbnail' => $story->thumbnail ?? null,
        ];
    }
}
