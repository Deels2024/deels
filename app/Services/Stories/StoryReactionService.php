<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Models\Comment;
use App\Models\Dislikes;
use App\Models\Likes;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class StoryReactionService
{
    public function like(Request $request): array
    {
        $userId = $request->user_id ?? Auth::id();
        $storyId = $request->story_id;
        $user = auth()->user() ?? User::find($request->user_id);
        if (!$user) {
            return [
                'success' => false,
                'errors' => 'Пользователь не найден',
            ];
        }

        $story = Story::withoutGlobalScopes()->find($storyId);
        if (!$story) {
            return [
                'success' => false,
                'errors' => 'Сториз не найден',
            ];
        }

        if ($story->challenge && $story->challenge->frozen) {
            return [
                'success' => false,
                'errors' => 'Челлендж заморожен. Вы не можете поставить лайк.',
            ];
        }

        $like = Likes::where('user_id', $userId)->where('story_id', $storyId)->first();
        $dislike = Dislikes::where('user_id', $userId)->where('story_id', $storyId)->first();
        if ($dislike) {
            $dislike->delete();
        }

        if ($like) {
            $like->delete();

            return $this->storyReactionPayload($story, 'Лайк убран');
        }

        $existingLike = Likes::where('story_id', $storyId)
            ->where('ip_address', $request->ip())
            ->first();

        if ($existingLike) {
            return [
                'success' => false,
                'errors' => 'Лайк уже добавлен с вашего IP-адреса',
            ];
        }

        Likes::create([
            'user_id' => $userId,
            'campaign_id' => 0,
            'story_id' => $storyId,
            'ip_address' => $request->ip() ?? null,
        ]);

        return $this->storyReactionPayload($story, 'Лайк добавлен');
    }

    public function dislike(Request $request): array
    {
        $userId = $request->user_id ?? Auth::id();
        $storyId = $request->story_id;
        $user = auth()->user() ?? User::find($request->user_id);
        if (!$user) {
            return [
                'success' => false,
                'errors' => 'Пользователь не найден',
            ];
        }

        $story = Story::withoutGlobalScopes()->find($storyId);
        if (!$story) {
            return [
                'success' => false,
                'errors' => 'Сториз не найден',
            ];
        }

        if ($story->battle) {
            if ($story->battle->frozen) {
                return [
                    'success' => false,
                    'errors' => 'Батл заморожен. Вы не можете поставить дизлайк.',
                ];
            }
        } else {
            return [
                'success' => false,
                'errors' => 'Батл не найден. Вы не можете поставить дизлайк.',
            ];
        }

        $like = Likes::where('user_id', $userId)->where('story_id', $storyId)->first();
        $dislike = Dislikes::where('user_id', $userId)->where('story_id', $storyId)->first();

        if ($like) {
            $like->delete();
        }

        if ($dislike) {
            $dislike->delete();

            return $this->storyReactionPayload($story, 'Дизлайк убран');
        }

        $existingDisLike = Dislikes::where('story_id', $storyId)
            ->where('ip_address', $request->ip())
            ->first();

        if ($existingDisLike) {
            return [
                'success' => false,
                'votes' => $story->votes,
                'votes_data' => $story->votes_data,
                'errors' => 'Дизлайк уже добавлен с вашего IP-адреса',
            ];
        }

        Dislikes::create([
            'user_id' => $userId,
            'campaign_id' => 0,
            'story_id' => $storyId,
            'ip_address' => $request->ip() ?? null,
        ]);

        return $this->storyReactionPayload($story, 'Дизлайк добавлен');
    }

    public function commentLike(Request $request): array
    {
        $userId = $request->user_id ?? Auth::id();
        $commentId = $request->comment_id;
        $user = auth()->user() ?? User::find($request->user_id);
        if (!$user) {
            return [
                'success' => false,
                'errors' => 'Пользователь не найден',
            ];
        }

        $comment = Comment::find($commentId);
        if (!$comment) {
            return [
                'success' => false,
                'errors' => 'Коммент не найден',
            ];
        }

        $like = Likes::where('user_id', $userId)->where('comment_id', $commentId)->first();
        if ($like) {
            $like->delete();

            return [
                'success' => true,
                'message' => 'Лайк убран',
                'count' => $comment->likes->count(),
            ];
        }

        Likes::create([
            'user_id' => $userId,
            'campaign_id' => 0,
            'comment_id' => $commentId,
            'ip_address' => $request->ip() ?? null,
        ]);

        return [
            'success' => true,
            'message' => 'Лайк добавлен',
            'count' => $comment->likes->count(),
        ];
    }

    private function storyReactionPayload(Story $story, string $message): array
    {
        return [
            'success' => true,
            'votes' => $story->votes,
            'votes_data' => $story->votes_data,
            'message' => $message,
        ];
    }
}
