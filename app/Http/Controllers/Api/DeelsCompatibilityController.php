<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Likes;
use App\Models\Payment;
use App\Models\Story;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeelsCompatibilityController extends Controller
{
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
