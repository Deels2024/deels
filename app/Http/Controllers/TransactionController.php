<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\Payment;
use App\Models\WithdrawalRequest;
use App\Services\ProjectWalletService;
use App\User;
use Bavix\Wallet\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $title = 'Транзакции дилсов';

        DB::statement("SET NAMES 'utf8'");
        $show_project_transactions = $request->boolean('show_project_transactions');
        $transactions = \App\Models\Transaction::query();

        if ($show_project_transactions) {
            $project_wallet = app(ProjectWalletService::class)->wallet();
            $transactions->where('wallet_id', $project_wallet->id);
        } else {
            $transactions->where('payable_type', 'App\Models\User');
        }

        if ($request->date_from) {
            $transactions->where('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $transactions->where('created_at', '<=', $request->date_to);
        }



        if(!$show_project_transactions && $request->user_id) {
            $transactions->where('payable_id', $request->user_id);
        }

        $transactions_count = clone $transactions;
        $withdrawals_count = clone $transactions;
        $withdrawals_deposit = $withdrawals_count->where('meta', 'like', '%Оплата за хранение сторис%')
            ->orWhere('meta', 'like', '%Оплата за стрим%')
            ->sum('amount');

        $withdrawals_deposit = abs(intval($withdrawals_deposit));
        $deposit = $transactions_count->where('amount', '>', 0)
            ->whereNull('meta')->sum('amount');
        $commission = ($deposit/100*0.2)+($withdrawals_deposit/100);

        if (request()?->has('excel')) {
            return $this->collectionToExcel($transactions->orderBy('id', 'desc')->get());
        }

        $transactions = $transactions->orderBy('id', 'desc')->paginate(100)->withQueryString();

        return view('admin.transactions', compact('title', 'transactions', 'deposit', 'commission', 'show_project_transactions'));
    }

    public function transactions_deposit(Request $request) {
        $user = \App\Models\User::find($request->user_id);
        $coins = $request->coins;

        if(!$user) {
            return redirect()->back()->with('error', 'Пользователь не найден');
        }
        if(!$coins) {
            return redirect()->back()->with('error', 'Укажите кол-во дилсов');
        }
        if($user) {
            $appstore_wallet = $user->getWallet('payments');
            if(!$appstore_wallet) {
                $user->createWallet([
                    'name' => 'Payments',
                    'slug' => 'payments',
                    'meta' => ['currency' => 'COINS'],
                ]);
            }
            $appstore_wallet = $user->getWallet('payments');
            if($appstore_wallet) {
                $balance = intval($appstore_wallet->balance ?? 0);
                $appstore_wallet->deposit($coins, ['get' => 'coins', 'balance_before' => $balance, 'description' => 'Пополнение']);
            }
        }
        return redirect()->back()->with('success', 'Баланс пополнен');
    }

}
