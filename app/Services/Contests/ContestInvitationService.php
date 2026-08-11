<?php

declare(strict_types=1);

namespace App\Services\Contests;

use Illuminate\Database\Eloquent\Model;

class ContestInvitationService
{
    public function permissions(Model $contest, string $type, ?int $userId): array
    {
        if (!$userId) {
            return ['allowed' => false, 'friends_only' => false];
        }

        $isOwner = (int) $contest->user_id === $userId;
        $isParticipant = !$isOwner && (bool) $contest->participant($userId);
        $isInvited = in_array($userId, $this->ids($contest->invite_user_ids ?? []), true);
        $visibility = $contest->visibility ?: ContestVisibilityService::ALL;

        if ($isOwner) {
            return ['allowed' => true, 'friends_only' => false];
        }

        if ($visibility === ContestVisibilityService::FRIENDS && $isParticipant) {
            return ['allowed' => true, 'friends_only' => true];
        }

        return [
            'allowed' => $visibility === ContestVisibilityService::ALL,
            'friends_only' => false,
            'invited' => $isInvited,
            'type' => $type,
        ];
    }

    public function ids(array $ids): array
    {
        return collect($ids)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->unique()
            ->values()
            ->all();
    }
}
