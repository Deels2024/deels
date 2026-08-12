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
use Illuminate\Http\Request;

class DeelsCompatibilityController extends Controller
{
    public function stats()
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

    public function challenge(Request $request, $id)
    {
        return app(ChallengeController::class)->get($request, $id);
    }

    public function story(Request $request, $id)
    {
        return app(StoryController::class)->get($request, $id);
    }

    public function wallet(Request $request)
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

    public function dialogs(Request $request)
    {
        return app(MessagesController::class)->get_list($request);
    }

    public function thread(Request $request, $id)
    {
        $request->merge(['thread_id' => $id]);

        return app(MessagesController::class)->show($request);
    }
}
