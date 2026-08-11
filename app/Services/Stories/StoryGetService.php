<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Models\Story;
use App\Models\User;
use Illuminate\Http\Request;
use App\Services\Contests\ContestVisibilityService;

class StoryGetService
{
    public function __construct(
        private StoryAccessService $accessService,
        private StoryAccountInfoService $accountInfoService,
        private StoryViewFormatter $formatter,
        private ContestVisibilityService $contestVisibility
    ) {
    }

    public function get(Request $request, $id, bool $onlyBody = false, bool $donate = true)
    {
        $story = Story::withoutGlobalScopes()->find($id);
        if (!$story || !$story->user) {
            return response()->json([
                'success' => false,
                'error' => 'Сторис не найдена',
            ]);
        }
        $contest = $story->challenge_id ? $story->challenge : ($story->battle_id ? $story->battle : null);
        if ($contest && !$this->contestVisibility->canView($contest, auth()->user())) {
            return response()->json([
                'success' => false,
                'error' => 'Просмотр недоступен',
            ], 403);
        }

        $userId = auth()->id() ?? $request->input('user_id');
        if ($story->paid && !$userId && !$onlyBody) {
            return response()->json([
                'success' => false,
                'error' => 'Необходима авторизация',
            ]);
        }

        $user = null;
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'error' => 'Пользователь не найден',
                ]);
            }
        }

        $access = $this->accessService->resolve($story, $user, $userId, (int) $id, $onlyBody, $donate);
        if (isset($access['response'])) {
            return $access['response'];
        }

        $data = $this->formatter->format(
            $story,
            $this->accountInfoService->build($story->user_id),
            $userId ? (int) $userId : null,
            $access['is_liked'],
            $access['is_viewed'],
            $access['blocked'],
            $access['show_story']
        );

        if ($onlyBody) {
            return $data;
        }

        if ($access['show_story']) {
            return response()->json([
                'success' => true,
                'data' => $data,
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Просмотр недоступен',
        ]);
    }
}
