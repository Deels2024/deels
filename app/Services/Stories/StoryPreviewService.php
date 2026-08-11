<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Models\User;
use Illuminate\Http\Request;

class StoryPreviewService
{
    public function __construct(private StoryGetService $storyGetService)
    {
    }

    public function preview(Request $request, $id): array
    {
        $data = $this->storyGetService->get($request, $id, true, false);
        $userId = $request->input('user_id');
        $user = $userId ? User::find($userId) : null;
        $authUser = $user;

        return [
            'success' => true,
            'data' => view('stories.modal_content', [
                'data' => $data,
                'user' => $user,
                'auth_user' => $authUser,
            ])->render(),
        ];
    }
}
