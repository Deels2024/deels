<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Controllers\FollowController;
use App\Http\Controllers\UserController;
use App\Models\Likes;
use App\Models\Story;
use App\Models\User;
use App\Services\ApiAccountInfoService;
use App\Services\Stories\StoryCommentService;
use App\Services\Stories\StoryReactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeelsSocialCompatibilityController extends Controller
{
    public function profile(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->userData((int) $request->user()->id),
        ]);
    }

    public function updateProfile(Request $request): JsonResponse
    {
        $legacy = app(UserController::class)->api_update($request);
        $payload = $legacy->getData(true);

        if (empty($payload['success'])) {
            return response()->json([
                'success' => false,
                'message' => 'Проверьте заполненные поля',
                'errors' => $payload['errors'] ?? [],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $this->userData((int) $request->user()->id),
        ]);
    }

    public function avatar(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'avatar' => ['required', 'file', 'image', 'max:10000'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Проверьте файл изображения',
                'errors' => $validator->errors(),
            ], 422);
        }

        $legacy = app(UserController::class)->api_update($request);
        $payload = $legacy->getData(true);
        if (empty($payload['success'])) {
            return response()->json($payload, 422);
        }

        return response()->json([
            'success' => true,
            'data' => $this->userData((int) $request->user()->id),
        ]);
    }

    public function user(Request $request, $id): JsonResponse
    {
        if (!User::find((int) $id)) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $this->userData((int) $id),
        ]);
    }

    public function userContent(Request $request, $id): JsonResponse
    {
        if (!User::find((int) $id)) {
            return response()->json(['message' => 'Пользователь не найден'], 404);
        }

        $query = Story::withoutGlobalScopes()
            ->where('user_id', (int) $id)
            ->where('active', true)
            ->where('declined', false)
            ->whereNull('blocked_at')
            ->whereNull('withdrawn_at')
            ->latest('created_at');

        $page = $query->paginate(max(1, min(50, (int) $request->input('limit', 20))));
        $rows = collect($page->items())->map(function (Story $story): array {
            return [
                'id' => $story->id,
                'story_id' => $story->id,
                'title' => $story->challenge?->title ?? $story->description ?? 'Публикация Deels',
                'description' => $story->description,
                'media_url' => $story->video_preview ?? ($story->media ? route('stories.get.video', $story->id) : $story->getFile()),
                'likes_count' => $story->likes_count ?? $story->likes()->count(),
                'comments_count' => $story->comments_count ?? $story->comments()->count(),
                'author' => $this->userData((int) $story->user_id),
                'created_at' => $story->created_at,
            ];
        })->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'meta' => [
                'total' => $page->total(),
                'current_page' => $page->currentPage(),
                'next_page' => $page->hasMorePages() ? $page->currentPage() + 1 : null,
            ],
        ]);
    }

    public function follow(Request $request, $id): JsonResponse
    {
        $request->merge([
            'user_id' => (int) $request->user()->id,
            'follow_id' => (int) $id,
        ]);

        return app(FollowController::class)->follow_toggle($request);
    }

    public function like(Request $request, string $type, $id): JsonResponse
    {
        if (!$this->isStorySocialType($type)) {
            return response()->json(['message' => 'Тип контента не поддерживается'], 422);
        }

        $request->merge([
            'user_id' => (int) $request->user()->id,
            'story_id' => (int) $id,
        ]);
        $payload = app(StoryReactionService::class)->like($request);

        return response()->json($payload, empty($payload['success']) ? 422 : 200);
    }

    public function unlike(Request $request, string $type, $id): JsonResponse
    {
        if (!$this->isStorySocialType($type)) {
            return response()->json(['message' => 'Тип контента не поддерживается'], 422);
        }

        Likes::where('user_id', (int) $request->user()->id)
            ->where('story_id', (int) $id)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function comment(Request $request, string $type, $id): JsonResponse
    {
        if (!$this->isStorySocialType($type)) {
            return response()->json(['message' => 'Тип контента не поддерживается'], 422);
        }

        $request->merge([
            'user_id' => (int) $request->user()->id,
            'story_id' => (int) $id,
            'comment' => $request->input('text'),
        ]);
        $payload = app(StoryCommentService::class)->create($request);

        return response()->json($payload, empty($payload['success']) ? 422 : 200);
    }

    public function share(Request $request, string $type, $id): JsonResponse
    {
        if (!$this->isStorySocialType($type)) {
            return response()->json(['message' => 'Тип контента не поддерживается'], 422);
        }

        if (!Story::withoutGlobalScopes()->find((int) $id)) {
            return response()->json(['message' => 'Контент не найден'], 404);
        }

        return response()->json(['success' => true]);
    }

    private function isStorySocialType(string $type): bool
    {
        return in_array($type, ['stories', 'story', 'challenges', 'challenge-responses'], true);
    }

    private function userData(int $id): array
    {
        return app(ApiAccountInfoService::class)->build($id, true) ?: (User::find($id)?->toArray() ?? []);
    }
}
