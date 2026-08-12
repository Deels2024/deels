<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Controller;
use App\Models\Likes;
use App\Models\Payment;
use App\Models\Story;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\ApiAccountInfoService;
use App\Services\ApiTokenAuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeelsCompatibilityController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $validator = validator($request->all(), [
            'login' => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Проверьте заполненные поля',
                'errors' => $validator->errors(),
            ], 422);
        }

        $login = trim((string) $request->input('login'));
        $request->merge([
            'email' => $login,
            'device_name' => $request->input('device_name', 'deels-new-web'),
        ]);

        $result = app(ApiTokenAuthService::class)->createToken($request);
        $payload = $result['payload'];
        $status = (int) $result['status'];

        if (empty($payload['success'])) {
            if ($status === 200) {
                $status = 401;
            }
            return response()->json([
                'success' => false,
                'message' => $payload['error'] ?? 'Не удалось войти',
                'error' => $payload['error'] ?? 'Не удалось войти',
                'retry_after' => $payload['retry_after'] ?? null,
            ], $status);
        }

        $loginField = filter_var($login, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $user = User::where($loginField, $login)->firstOrFail();
        $userData = app(ApiAccountInfoService::class)->build((int) $user->id, true) ?: $user->toArray();

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $userData,
                'access_token' => $payload['access_token'],
                'token_type' => $payload['token_type'] ?? 'Bearer',
            ],
        ]);
    }

    public function register(Request $request): JsonResponse
    {
        $consentValidator = validator($request->all(), [
            'terms_accepted' => 'accepted',
            'privacy_accepted' => 'accepted',
            'content_rules_accepted' => 'accepted',
        ]);

        if ($consentValidator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Необходимо принять обязательные документы',
                'errors' => $consentValidator->errors(),
            ], 422);
        }

        $request->merge([
            'password_confirmation' => $request->input('password_confirmation'),
            'username' => $request->input('username'),
        ]);

        $legacy = app(RegisterController::class)->api_register($request);
        $payload = $legacy->getData(true);

        if (empty($payload['success'])) {
            return response()->json([
                'success' => false,
                'message' => $payload['error'] ?? 'Проверьте заполненные поля',
                'error' => $payload['error'] ?? null,
                'errors' => $payload['errors'] ?? [],
            ], 422);
        }

        $user = User::find((int) ($payload['user_id'] ?? 0));
        $userData = $user
            ? (app(ApiAccountInfoService::class)->build((int) $user->id, true) ?: $user->toArray())
            : [];

        return response()->json([
            'success' => true,
            'data' => [
                'user' => $userData,
                'access_token' => $payload['access_token'] ?? null,
                'token_type' => $payload['token_type'] ?? 'Bearer',
                'email_verification_required' => false,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $token = $request->user()?->currentAccessToken();
        if ($token) {
            $token->delete();
        }

        return response()->json(['success' => true]);
    }

    public function stats(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => [
                'users_count' => User::count(),
                'responses_count' => Story::active()->count(),
                'votes_count' => Likes::count(),
                'campaigns_amount' => (float) Payment::where('status', 'success')->sum('amount'),
            ],
        ]);
    }

    public function feed(Request $request)
    {
        return app(ApiController::class)->get_stories($request);
    }

    public function stories(Request $request)
    {
        return app(ApiController::class)->get_stories($request);
    }

    public function challenge(Request $request, $id): JsonResponse
    {
        $data = app(ChallengeController::class)->get($request, $id, true, false);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $data['id'] = $data['challenge_id'] ?? $id;
        $data['prize_amount'] = $data['reward_amount'] ?? 0;
        $data['media_url'] = $data['video_preview'] ?? $data['path'] ?? null;

        $user = $request->user();
        $saved = false;
        if ($user) {
            $meta = is_array($user->meta_data) ? $user->meta_data : [];
            $saved = collect($meta['saved_challenge_ids'] ?? [])
                ->map(fn ($value) => (int) $value)
                ->contains((int) $id);
        }
        $data['saved'] = $saved;
        $data['is_saved'] = $saved;

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function story(Request $request, $id): JsonResponse
    {
        $data = app(StoryController::class)->get($request, $id, true, false);
        if ($data instanceof JsonResponse) {
            return $data;
        }

        $data['id'] = $data['story_id'] ?? $id;
        $data['media_url'] = $data['video_preview'] ?? $data['path'] ?? null;

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function wallet(Request $request): JsonResponse
    {
        $user = $request->user();
        $wallet = $user->getWallet('default');

        $transactions = collect();
        if ($wallet) {
            $transactions = Transaction::where('wallet_id', $wallet->id)
                ->latest('created_at')
                ->limit(20)
                ->get()
                ->map(function (Transaction $transaction): array {
                    $meta = is_array($transaction->meta)
                        ? $transaction->meta
                        : (json_decode((string) $transaction->meta, true) ?: []);

                    return [
                        'id' => $transaction->id,
                        'amount' => $transaction->amount,
                        'direction' => ((float) $transaction->amount >= 0) ? 'credit' : 'debit',
                        'title' => $meta['description'] ?? $meta['operation'] ?? 'Операция',
                        'description' => $meta['description'] ?? null,
                        'created_at' => $transaction->created_at,
                    ];
                });
        }

        $pending = WithdrawalRequest::where('wallet', true)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->sum('amount');

        return response()->json([
            'success' => true,
            'data' => [
                'balance' => (float) ($wallet->balance ?? 0),
                'available_balance' => (float) ($wallet->balance ?? 0),
                'pending_balance' => (float) $pending,
                'transactions' => $transactions->values(),
            ],
        ]);
    }

    public function dialogs(Request $request): JsonResponse
    {
        $legacy = app(MessagesController::class)->get_list($request);
        $payload = $legacy->getData(true);

        if (empty($payload['success'])) {
            return response()->json([
                'success' => true,
                'data' => [],
                'total' => 0,
            ]);
        }

        $rows = collect($payload['data'] ?? [])->map(function (array $row): array {
            $row['time'] = $row['time'] ?? $row['date'] ?? '';
            return $row;
        })->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'total' => $rows->count(),
        ]);
    }

    public function thread(Request $request, $id): JsonResponse
    {
        $request->merge(['thread_id' => $id]);
        $legacy = app(MessagesController::class)->show($request);
        $payload = $legacy->getData(true);

        if (empty($payload['success'])) {
            return response()->json($payload, $legacy->getStatusCode());
        }

        $rows = collect($payload['data'] ?? [])
            ->flatMap(function ($messages): array {
                return is_array($messages) ? $messages : [];
            })
            ->map(function (array $message): array {
                return [
                    'id' => $message['id'] ?? md5(($message['created_at'] ?? '') . '|' . ($message['message'] ?? '')),
                    'text' => $message['message'] ?? '',
                    'message' => $message['message'] ?? '',
                    'created_at' => $message['created_at'] ?? '',
                    'is_mine' => (bool) ($message['my_message'] ?? false),
                    'outgoing' => (bool) ($message['my_message'] ?? false),
                    'user' => $message['user'] ?? null,
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'data' => $rows,
            'total' => $rows->count(),
            'current_page' => $payload['current_page'] ?? 1,
            'total_pages' => $payload['total_pages'] ?? 1,
            'thread_id' => $payload['thread_id'] ?? $id,
            'is_blocked' => $payload['is_blocked'] ?? false,
        ]);
    }
}
