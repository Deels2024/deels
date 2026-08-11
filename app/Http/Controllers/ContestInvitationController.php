<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Controllers\Dashboard\ChallengeDashboardController;
use App\Models\Battle;
use App\Models\Challenge;
use App\Services\Contests\ContestInvitationService;
use App\Services\Contests\ContestNotificationService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContestInvitationController extends Controller
{
    public function users(
        Request $request,
        string $type,
        int $id,
        ContestInvitationService $invitations
    ): JsonResponse {
        $contest = $this->contest($type, $id);
        $permissions = $invitations->permissions($contest, $type, (int) $request->user()->id);
        abort_unless($permissions['allowed'], 403);

        if ($permissions['friends_only']) {
            $request->merge(['friends_only' => true]);
        }
        $request->merge(['exclude_ids' => (string) $contest->user_id]);

        return app(ChallengeDashboardController::class)->inviteUsers($request);
    }

    public function store(
        Request $request,
        string $type,
        int $id,
        ContestInvitationService $invitations,
        ContestNotificationService $notifications
    ): JsonResponse {
        $contest = $this->contest($type, $id);
        $permissions = $invitations->permissions($contest, $type, (int) $request->user()->id);
        abort_unless($permissions['allowed'], 403);

        $validated = $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'integer|distinct|exists:users,id',
        ]);
        $requestedIds = $invitations->ids($validated['user_ids']);
        $requestedIds = array_values(array_diff($requestedIds, [
            (int) $request->user()->id,
            (int) $contest->user_id,
        ]));

        if ($permissions['friends_only']) {
            $friendIds = $this->friendIds((int) $request->user()->id);
            if (array_diff($requestedIds, $friendIds)) {
                throw ValidationException::withMessages([
                    'user_ids' => 'Можно приглашать только своих друзей.',
                ]);
            }
        }

        [$contest, $newIds] = DB::transaction(function () use ($type, $id, $requestedIds, $invitations): array {
            $lockedContest = ($type === 'challenge' ? Challenge::query() : Battle::query())
                ->lockForUpdate()
                ->findOrFail($id);
            $existingIds = $invitations->ids($lockedContest->invite_user_ids ?? []);
            $newIds = array_values(array_diff($requestedIds, $existingIds));
            if (!$newIds) {
                throw ValidationException::withMessages([
                    'user_ids' => 'Все выбранные пользователи уже приглашены.',
                ]);
            }

            $lockedContest->forceFill([
                'invite_user_ids' => array_values(array_unique(array_merge($existingIds, $newIds))),
            ])->saveQuietly();

            return [$lockedContest, $newIds];
        });
        $notifications->notifyNewContestInvitees($contest, $type);

        return response()->json([
            'success' => true,
            'invited_ids' => $newIds,
        ]);
    }

    private function contest(string $type, int $id): Model
    {
        abort_unless(in_array($type, ['challenge', 'battle'], true), 404);

        return ($type === 'challenge' ? Challenge::query() : Battle::query())->findOrFail($id);
    }

    private function friendIds(int $userId): array
    {
        return DB::table('followables')
            ->where('followable_id', $userId)
            ->pluck('user_id')
            ->intersect(
                DB::table('followables')
                    ->where('user_id', $userId)
                    ->pluck('followable_id')
            )
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();
    }
}
