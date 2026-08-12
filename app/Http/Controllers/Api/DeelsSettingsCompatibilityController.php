<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DeelsSettingsCompatibilityController extends Controller
{
    private const PREFERENCE_KEYS = [
        'public_profile',
        'direct_messages',
        'show_city',
        'personalized_feed',
        'notify_activity',
        'notify_challenges',
        'notify_payments',
        'notify_marketing',
    ];

    public function preferences(Request $request): JsonResponse
    {
        $validator = validator($request->all(), array_fill_keys(self::PREFERENCE_KEYS, ['sometimes', 'boolean']));
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Проверьте настройки',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        $meta = is_array($user->meta_data) ? $user->meta_data : [];
        $current = is_array($meta['deels_preferences'] ?? null) ? $meta['deels_preferences'] : [];
        foreach ($validator->validated() as $key => $value) {
            $current[$key] = (bool) $value;
        }
        $meta['deels_preferences'] = $current;

        $updates = ['meta_data' => $meta];
        if ($request->has('direct_messages')) {
            $updates['first_message_followings_only'] = !$request->boolean('direct_messages');
        }
        $user->forceFill($updates)->save();

        return response()->json([
            'success' => true,
            'data' => $current,
        ]);
    }

    public function changePassword(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Проверьте пароль',
                'errors' => $validator->errors(),
            ], 422);
        }

        $user = $request->user();
        if (!Hash::check((string) $request->input('current_password'), (string) $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Текущий пароль указан неверно',
                'errors' => ['current_password' => ['Текущий пароль указан неверно']],
            ], 422);
        }

        $user->forceFill(['password' => Hash::make((string) $request->input('password'))])->save();

        return response()->json(['success' => true]);
    }

    public function sessions(Request $request): JsonResponse
    {
        $currentToken = $request->user()->currentAccessToken();
        $rows = $request->user()->tokens()
            ->latest('last_used_at')
            ->latest('created_at')
            ->get()
            ->map(function ($token) use ($currentToken): array {
                return [
                    'id' => $token->id,
                    'name' => $token->name,
                    'created_at' => $token->created_at,
                    'last_used_at' => $token->last_used_at,
                    'current' => $currentToken && (int) $currentToken->id === (int) $token->id,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'total' => $rows->count(),
        ]);
    }

    public function closeOtherSessions(Request $request): JsonResponse
    {
        $currentToken = $request->user()->currentAccessToken();
        $query = $request->user()->tokens();
        if ($currentToken) {
            $query->where('id', '!=', $currentToken->id);
        }
        $closed = $query->delete();

        return response()->json([
            'success' => true,
            'data' => ['closed' => $closed],
        ]);
    }
}
