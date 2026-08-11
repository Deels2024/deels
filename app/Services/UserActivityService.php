<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;

class UserActivityService
{
    public function __construct(private UserRequestContextService $requestContextService)
    {
    }

    public function updateApiRequestData(User $user, Request $request): void
    {
        $data = [
            'user_data' => $this->requestContextService->build($request, true),
            'ip_address' => $request->ip(),
        ];

        if ($this->canConfirmActivity($user)) {
            $data['need_action_at'] = null;
        }

        $user->update($data);
    }

    public function updateWebActivity(User $user, Request $request): void
    {
        $data = [
            'last_active' => now(),
            'ip_address' => $request->ip(),
            'user_data' => json_encode($this->requestContextService->build($request, false, 'Desktop')),
        ];

        if ($this->canConfirmActivity($user)) {
            $data['need_action_at'] = null;
        }

        $user->update($data);
    }

    private function canConfirmActivity(User $user): bool
    {
        return $user->need_action_at !== null && now()->gte($user->need_action_at);
    }
}
