<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Jobs\FireBaseEvent;
use App\Models\Story;
use App\Models\User;
use App\Services\ReferralBonusService;
use Illuminate\Http\Request;

class StoryDonationService
{
    public function donate(Request $request, $id, callable $storyGetter): array
    {
        $data = $storyGetter($request, $request->input('story_id'), false, true)->getData();
        $view = null;
        $user = null;
        $authUser = null;
        $userId = $request->input('user_id');
        if ($userId) {
            $user = User::find($userId);
            $authUser = $user;
        }

        if ($data->success) {
            \App\Models\View::create([
                'user_id' => $userId,
                'story_id' => $id,
            ]);
            $viewData = json_encode($data->data, JSON_UNESCAPED_UNICODE);
            $viewData = json_decode($viewData, true);
            $view = view('stories.modal_content', ['data' => $viewData, 'auth_user' => $authUser, 'user' => $user])->render();
        }

        return [
            'success' => $data->success,
            'error' => $data->error ?? null,
            'data' => $view,
        ];
    }

    public function pay(Request $request, $id = null)
    {
        $userId = $request->input('user_id') ?? request()->user()->id ?? null;
        $story = Story::find($request->input('story_id'));
        $amount = $request->input('amount');
        if ($amount <= 0) {
            return [
                'success' => false,
                'error' => 'Укажите сумму',
            ];
        }

        $user = null;
        if ($userId) {
            $user = User::find($userId);
        }
        if (!$story) {
            return [
                'success' => false,
                'error' => 'Сторис не найдена',
            ];
        }

        if (!$user) {
            return [
                'success' => false,
                'error' => 'Пользователь не найден',
            ];
        }

        try {
            $storyOwner = $story->user;
            $user->wallet_withdraw(intval($amount), ['donate' => 'story', 'description' => 'Донат в сторис #' . $story->id]);
            app(ReferralBonusService::class)->awardForFirstDonate($user);
            $storyOwner->deposit($amount, ['get' => 'coins', 'description' => 'Донат в сторис #' . $story->id]);
            FireBaseEvent::dispatch($storyOwner->id, 'В вашу сторис внесли донат!', $story->id, 'story');
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'amount' => $story->amount,
                'balance' => intval($user->balance),
                'error' => $e->getMessage(),
            ];
        }

        return [
            'success' => true,
        ];
    }
}
