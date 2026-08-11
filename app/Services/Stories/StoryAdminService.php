<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Helpers\AppHelper;
use App\Models\Battle;
use App\Models\Campaign;
use App\Models\Challenge;
use App\Models\Comment;
use App\Models\ContestReport;
use App\Models\Dislikes;
use App\Models\Likes;
use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StoryAdminService
{
    public function storiesList(Request $request, ?User $user, ?int $authUserId): array
    {
        $stories = [];
        $type = $request->input('type');
        $aiModerated = $request->input('ai_moderated');
        $storyId = $request->input('story_id');

        if ($user && ($user->is_admin() || $user->is_comment_admin())) {
            $query = Story::query()->excludeBlockedAuthors($authUserId);
            if ($storyId) {
                $query->where('id', $storyId);
            }

            if ($type === 'declined') {
                $query->where('declined', true);
            } elseif ($type === 'active') {
                $query->where('active', true)->where('declined', false);
            } elseif ($type === 'blocked') {
                $query->whereNotNull('blocked_at');
            } else {
                $query->where('active', false)->where('declined', false);
            }

            if ($storyId) {
                $query->orWhere('id', $storyId);
            }

            if ($aiModerated) {
                $query->where('ai_moderated', true);
            }

            $stories = $query->orderBy('id', 'desc')->paginate(12);
        }

        return [
            'title' => 'Модерация сторис',
            'stories' => $stories,
        ];
    }

    public function challengeStoriesList(Request $request, ?User $user, ?int $authUserId): array
    {
        $stories = [];
        $type = $request->input('type');
        $storyId = $request->input('story_id');
        $challengeId = $request->input('challenge_id');

        if ($user && ($user->is_admin() || $user->is_comment_admin())) {
            $query = Story::query()
                ->excludeBlockedAuthors($authUserId)
                ->whereNotNull('challenge_id')
                ->where(function ($query): void {
                    $query->where('is_main_story', false)
                        ->orWhereNull('is_main_story');
                });

            if ($storyId) {
                $query->where('id', $storyId);
            }
            if ($challengeId) {
                $query->where('challenge_id', $challengeId);
            }

            if ($type === 'frozen') {
                $query->where('frozen', true)->where('banned', false);
            } elseif ($type === 'banned') {
                $query->where('banned', true);
            }

            $stories = $query->orderBy('id', 'desc')->paginate(12);
        }

        return [
            'title' => 'Модерация ответов на челленджи',
            'stories' => $stories,
        ];
    }

    public function likesList(Request $request, ?int $userId): array
    {
        $type = $request->input('type');
        $campaigns = [];
        $comments = [];
        $stories = [];

        if (!$type || $type === 'stories') {
            $storyIds = Likes::where('story_id', '>', 0)
                ->where('user_id', $userId)
                ->whereHas('story')
                ->pluck('story_id')
                ->toArray();
            $stories = Story::whereIn('id', $storyIds)->paginate(20);
        }

        if ($type === 'campaigns') {
            $campaignIds = Likes::where('campaign_id', '>', 0)
                ->where('user_id', $userId)
                ->whereHas('campaign')
                ->pluck('campaign_id')
                ->toArray();
            $campaigns = Campaign::whereIn('id', $campaignIds)->paginate(20);
        }

        if ($type === 'comments') {
            $commentIds = Likes::where('comment_id', '>', 0)
                ->where('user_id', $userId)
                ->pluck('comment_id')
                ->toArray();
            $comments = Comment::whereIn('id', $commentIds)->paginate(20);
        }

        return [
            'title' => 'Лайки',
            'stories' => $stories,
            'campaigns' => $campaigns,
            'comments' => $comments,
        ];
    }

    public function confirm(Request $request, User $user): array
    {
        $story = Story::withoutGlobalScopes()->find($request->story_id);
        if (!$story || ($user->id != $story->user_id && !$user->is_admin() && !$user->is_comment_admin())) {
            return ['success' => false];
        }

        $helper = new AppHelper();
        switch ($request->action) {
            case 'approve':
                $helper->story_approve($story);
                break;
            case 'trash':
                $helper->story_decline($story);
                break;
            case 'delete':
                $story->delete();
                break;
        }

        return ['success' => 1];
    }

    public function confirmChallengeStory(Request $request, User $user): array
    {
        $story = Story::find($request->story_id);
        if (!$story || ($user->id != $story->user_id && !$user->is_admin() && !$user->is_comment_admin())) {
            return ['success' => false];
        }

        switch ($request->action) {
            case 'frozen':
                $story->update(['frozen' => true]);
                break;
            case 'banned':
                $story->update(['banned' => true, 'frozen' => false]);
                break;
            case 'approved':
                $story->update(['banned' => false, 'frozen' => false]);
                break;
        }

        return ['success' => 1];
    }

    public function remove(Request $request, ?User $user): array
    {
        $story = Story::withoutGlobalScopes()->find($request->story_id);
        if (!$user) {
            return ['success' => false, 'error' => 'Пользователь не найден'];
        }
        if (!$story) {
            return ['success' => false, 'error' => 'Сторис не найдена'];
        }
        if ($user->id != $story->user_id && !$user->is_admin() && !$user->is_comment_admin()) {
            return ['success' => false, 'error' => 'Вы не можете удалить эту сторис'];
        }
        $isContestReport = ContestReport::where('story_id', $story->id)
            ->where('user_id', $user->id)
            ->exists();
        if (!$isContestReport && $story->active && $story->challenge) {
            if ($story->challenge->started) {
                return ['success' => false, 'error' => 'Вы не можете удалить эту сторис. Челлендж уже запущен.'];
            }
            if ($story->challenge->finished) {
                return ['success' => false, 'error' => 'Вы не можете удалить эту сторис. Челлендж уже завершен.'];
            }
        }

        $story->delete();

        return ['success' => true, 'message' => 'Сторис удалена'];
    }

    public function repost(Request $request, ?User $user): array
    {
        $receivers = $request->input('receivers');
        $userId = $request->input('user_id');
        if (!$user || $user->id != $userId) {
            return ['success' => false, 'message' => 'Некорректный запрос'];
        }

        $story = Story::find($request->input('story_id'));
        if (!$story) {
            return ['success' => false, 'message' => 'Сторис не найдена'];
        }

        $helper = new AppHelper();
        $button = [
            'type' => 'story',
            'story_id' => $story->id,
            'text' => 'Перейти',
            'url' => $story->getStoryShareUrl(),
        ];
        foreach ($receivers as $receiver) {
            $helper->direct_chat($user, $receiver, 'Посмотри эту сторис', $button);
        }

        return ['success' => true, 'message' => 'Вы успешно поделились!'];
    }

    public function storiesLikes(Request $request): array
    {
        $storyId = $request->input('story_id');
        $challengeId = $request->input('challenge');
        $battleId = $request->input('battle');
        $story = Story::find($storyId);
        $query = Likes::with('story');

        if ($story) {
            $query->where('story_id', $story->id);
        }
        if ($challengeId) {
            $query->whereHas('story', function ($query) use ($challengeId): void {
                $query->where('challenge_id', $challengeId)
                    ->where(function ($query): void {
                        $query->where('is_main_story', false)
                            ->orWhereNull('is_main_story');
                    });
            });
        }
        if ($battleId) {
            $query->whereHas('story', function ($query) use ($battleId): void {
                $query->withoutGlobalScopes()
                    ->where('battle_id', $battleId)
                    ->where(function ($query): void {
                        $query->where('is_main_story', false)
                            ->orWhereNull('is_main_story');
                    });
            });
        }

        return [
            'story' => $story,
            'likes' => $query->orderBy('created_at', 'DESC')->paginate(50),
            'battles' => Battle::orderBy('id', 'DESC')->get(),
            'challenges' => Challenge::orderBy('id', 'DESC')->get(),
        ];
    }

    public function storiesDislikes(Request $request): array
    {
        $storyId = $request->input('story_id');
        $battleId = $request->input('battle');
        $story = Story::find($storyId);
        $query = Dislikes::with('story');

        if ($story) {
            $query->where('story_id', $story->id);
        }
        if ($battleId) {
            $query->whereHas('story', function ($query) use ($battleId): void {
                $query->withoutGlobalScopes()
                    ->where('battle_id', $battleId)
                    ->where(function ($query): void {
                        $query->where('is_main_story', false)
                            ->orWhereNull('is_main_story');
                    });
            });
        }

        return [
            'story' => $story,
            'dislikes' => $query->orderBy('created_at', 'DESC')->paginate(50),
            'battles' => Battle::orderBy('id', 'DESC')->get(),
        ];
    }

    public function addLikes(Request $request, ?User $user): void
    {
        $storyId = $request->input('story_id');
        $likes = (int) $request->input('likes');

        if (!$user || !$user->is_admin()) {
            return;
        }

        for ($i = 1; $i <= $likes; $i++) {
            DB::table('likes')->insert([
                'story_id' => $storyId,
                'campaign_id' => 0,
                'user_id' => $user->id,
            ]);

            for ($commentI = 1; $commentI <= rand(1, 4); $commentI++) {
                DB::table('views')->insert([
                    'story_id' => $storyId,
                    'campaign_id' => 0,
                    'user_id' => $user->id,
                ]);
            }
        }
    }
}
