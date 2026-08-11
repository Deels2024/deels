<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Jobs\FireBaseEvent;
use App\Models\Comment;
use App\Models\Story;
use App\Models\User;
use App\Notifications\UserEmail;
use Illuminate\Http\Request;

class StoryCommentService
{
    public function create(Request $request): array
    {
        $validator = validator($request->all(), [
            'story_id' => 'required',
            'comment' => 'required',
        ]);
        if ($validator->fails()) {
            return [
                'success' => false,
                'errors' => $validator->errors()->first(),
            ];
        }

        $user = auth()->user() ?? User::find($request->user_id);
        if (!$user) {
            return [
                'success' => false,
                'errors' => 'Пользователь не найден',
            ];
        }

        $story = Story::withoutGlobalScopes()->find($request->story_id);
        if (!$story) {
            return [
                'success' => false,
                'errors' => 'Сторис не найдена',
            ];
        }

        if ($story->challenge && $story->challenge->frozen) {
            return [
                'success' => false,
                'errors' => 'Челлендж заморожен. Вы не можете оставить комментарий.',
            ];
        }

        $postComment = Comment::create([
            'user_id' => $request->user_id,
            'campaign_id' => 0,
            'story_id' => $request->story_id,
            'comment_id' => 0,
            'author_name' => $user->name,
            'author_email' => $user->email,
            'author_ip' => null,
            'comment' => $request->comment,
            'approved' => 1,
        ]);

        $text = 'Вам оставили комментарий на сторис №' . $story->id . '.<br>Перейдите в личный кабинет на <a href="' . url('/') . '">deels.ru</a>,чтобы его посмотреть';
        $story->user->notify(new UserEmail('Вам оставили комментарий на сторис №' . $story->id, $text));
        FireBaseEvent::dispatch($story->user->id, 'Посмотрите новый комментарий к вашей сторис!', $story->id, 'story');

        return [
            'success' => true,
            'comment_id' => $postComment->id,
            'message' => 'Комментарий опубликован',
        ];
    }
}
