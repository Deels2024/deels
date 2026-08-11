<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Jobs\FireBaseEvent;
use App\Models\FriendSuggestion;
use App\Models\User;
use App\Models\UserContactHash;
use App\Models\UserContactImport;
use App\Models\UserVkFriend;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContactsController extends Controller
{
    public function promptState(Request $request)
    {
        $user = $request->user();
        $state = $this->contactImport($user->id);

        return response()->json([
            'success' => true,
            'data' => [
                'status' => $state->status,
                'source' => $state->source,
                'first_confirmed_at' => optional($state->first_confirmed_at)->toISOString(),
                'last_denied_at' => optional($state->last_denied_at)->toISOString(),
                'next_prompt_at' => optional($state->next_prompt_at)->toISOString(),
                'can_prompt_now' => !$state->next_prompt_at || $state->next_prompt_at->isPast(),
            ],
        ]);
    }

    public function deny(Request $request)
    {
        $state = $this->contactImport($request->user()->id);
        $state->fill([
            'status' => 'denied',
            'last_denied_at' => now(),
            'next_prompt_at' => now()->addHours(12),
        ])->save();

        return response()->json([
            'success' => true,
            'data' => [
                'next_prompt_at' => $state->next_prompt_at->toISOString(),
            ],
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'phone_hashes' => 'required|array|max:5000',
            'phone_hashes.*' => ['required', 'string', 'regex:/^[a-f0-9]{64}$/i'],
        ]);

        $user = $request->user();
        $hashes = $this->uniqueValues($request->input('phone_hashes'));

        DB::transaction(function () use ($user, $hashes): void {
            $state = $this->contactImport($user->id);
            $state->fill([
                'status' => 'allowed',
                'source' => $state->source === 'vk' ? 'mixed' : 'phonebook',
                'first_confirmed_at' => $state->first_confirmed_at ?? now(),
                'next_prompt_at' => null,
            ])->save();

            $this->storePhoneHashes($user->id, $hashes);
            $this->createPhoneSuggestions($user, $hashes);
        });

        return response()->json([
            'success' => true,
            'found_users' => $this->suggestedUsers($user),
            'show_friends_popup' => FriendSuggestion::where('user_id', $user->id)
                ->whereNull('followed_at')
                ->exists(),
        ]);
    }

    public function importVkFriends(Request $request)
    {
        $request->validate([
            'vk_friend_ids' => 'required|array|max:10000',
            'vk_friend_ids.*' => 'required|string|max:64',
        ]);

        $user = $request->user();
        $vkIds = $this->uniqueValues($request->input('vk_friend_ids'));

        $this->syncVkFriends($user, $vkIds);

        return response()->json([
            'success' => true,
            'found_users' => $this->suggestedUsers($user),
            'show_friends_popup' => FriendSuggestion::where('user_id', $user->id)
                ->whereNull('followed_at')
                ->exists(),
        ]);
    }

    public function syncVkFriends(User $user, array $vkIds): void
    {
        $vkIds = $this->uniqueValues($vkIds);

        if (!$vkIds) {
            return;
        }

        DB::transaction(function () use ($user, $vkIds): void {
            $state = $this->contactImport($user->id);
            $state->fill([
                'source' => in_array($state->source, ['phonebook', 'mixed'], true) ? 'mixed' : 'vk',
            ])->save();

            $this->storeVkFriends($user->id, $vkIds);
            $this->createVkSuggestions($user, $vkIds);
        });
    }

    public function getSuggestedUsers(User $user)
    {
        return $this->suggestedUsers($user);
    }

    public function suggestions(Request $request)
    {
        return response()->json([
            'success' => true,
            'found_users' => $this->suggestedUsers($request->user()),
        ]);
    }

    public function followSuggestions(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|max:500',
            'user_ids.*' => 'required|integer|exists:users,id',
        ]);

        $user = $request->user();
        $ids = array_map('intval', $request->input('user_ids'));

        $suggestions = FriendSuggestion::where('user_id', $user->id)
            ->whereIn('suggested_user_id', $ids)
            ->whereNull('followed_at')
            ->with('suggestedUser')
            ->get();

        $followedIds = [];

        foreach ($suggestions as $suggestion) {
            $suggestedUser = $suggestion->suggestedUser;

            if (!$suggestedUser || $suggestedUser->id === $user->id || $user->isFollowing($suggestedUser)) {
                continue;
            }

            $user->follow($suggestedUser);
            $suggestedUser->acceptFollowRequestFrom($user);
            $suggestion->forceFill(['followed_at' => now()])->save();
            $followedIds[] = $suggestedUser->id;
        }

        return response()->json([
            'success' => true,
            'followed_user_ids' => $followedIds,
        ]);
    }

    private function contactImport(int $userId): UserContactImport
    {
        return UserContactImport::firstOrCreate(
            ['user_id' => $userId],
            ['status' => 'pending']
        );
    }

    private function storePhoneHashes(int $userId, array $hashes): void
    {
        if (!$hashes) {
            return;
        }

        $now = now();
        $rows = array_map(fn (string $hash): array => [
            'user_id' => $userId,
            'phone_hash' => strtolower($hash),
            'created_at' => $now,
            'updated_at' => $now,
        ], $hashes);

        UserContactHash::upsert($rows, ['user_id', 'phone_hash'], ['updated_at']);
    }

    private function storeVkFriends(int $userId, array $vkIds): void
    {
        if (!$vkIds) {
            return;
        }

        $now = now();
        $rows = array_map(fn (string $vkId): array => [
            'user_id' => $userId,
            'vk_id' => $vkId,
            'created_at' => $now,
            'updated_at' => $now,
        ], $vkIds);

        UserVkFriend::upsert($rows, ['user_id', 'vk_id'], ['updated_at']);
    }

    private function createPhoneSuggestions(User $user, array $hashes): void
    {
        $matchedIds = User::whereIn('phone_hash', array_map('strtolower', $hashes))
            ->where('id', '!=', $user->id)
            ->pluck('id')
            ->all();

        $this->createSuggestions($user, $matchedIds, 'phone');
    }

    private function createVkSuggestions(User $user, array $vkIds): void
    {
        $matchedIds = User::whereIn('vk_id', $vkIds)
            ->where('id', '!=', $user->id)
            ->pluck('id')
            ->all();

        $this->createSuggestions($user, $matchedIds, 'vk');
    }

    private function createSuggestions(User $user, array $matchedIds, string $source): void
    {
        $now = now();

        foreach (array_unique($matchedIds) as $matchedId) {
            $suggestion = FriendSuggestion::firstOrCreate(
                [
                    'user_id' => $user->id,
                    'suggested_user_id' => $matchedId,
                ],
                [
                    'source' => $source,
                ]
            );

            if (!$suggestion->notified_at) {
//                FireBaseEvent::dispatch(
//                    $matchedId,
//                    'Ваш друг ' . $user->username . ' присоединился к Deels. Подписаться на него?',
//                    $user->id,
//                    'follow_user'
//                );

                try {
                    $matchedUser = User::find($matchedId);
                    if ($matchedUser) {
                        $button = '<a href="' . url('/profile/' . $user->id) . '" class="btn btn-small">Подписаться</a>';
                        (new AppHelper())->chat_notify(
                            $matchedUser,
                            'Ваш друг ' . $user->username . ' присоединился к Deels. Подписаться на него?',
                            $button
                        );
                    }
                } catch (\Throwable $e) {
                }

                $suggestion->forceFill(['notified_at' => $now])->save();
            }
        }
    }

    private function suggestedUsers(User $user)
    {
        return FriendSuggestion::where('user_id', $user->id)
            ->whereNull('followed_at')
            ->with('suggestedUser:id,username,avatar,email')
            ->get()
            ->filter(fn (FriendSuggestion $suggestion): bool => $suggestion->suggestedUser !== null)
            ->map(fn (FriendSuggestion $suggestion): array => [
                'id' => $suggestion->suggestedUser->id,
                'username' => $suggestion->suggestedUser->username,
                'avatar' => url($suggestion->suggestedUser->avatar()),
                'source' => $suggestion->source,
            ])
            ->values();
    }

    private function uniqueValues(array $values): array
    {
        return array_values(array_unique(array_filter(array_map(
            fn ($value): string => trim((string) $value),
            $values
        ))));
    }
}
