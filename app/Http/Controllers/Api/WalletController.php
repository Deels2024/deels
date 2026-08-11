<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Helpers\AppHelper;
use App\Http\Controllers\Controller;
use App\Models\Faq;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Services\Tinkoff\TinkoffEacqApi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Kenvel\Tinkoff;
use Imdhemy\Purchases\Facades\Product;

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function transactions(Request $request)
    {
        $user = request()->user();
        $type = $request->input('type');
        $payments_wallet = $user->getWallet('payments');
        $default_wallet = $user->getWallet('default');
        $data = [];
        if($payments_wallet && $type && $type == 'payments') {
            $transactions = Transaction::where('wallet_id', $payments_wallet->id)->orderBy('created_at', 'DESC')->paginate(5);
            foreach ($transactions as $payments_wallet_transaction) {
                $data[] = $payments_wallet_transaction;
            }
        } else {
            if($default_wallet) {
                $transactions = Transaction::where('wallet_id', $default_wallet->id)->orderBy('created_at', 'DESC')->paginate(5);
                foreach ($transactions as $transaction) {
                    $data[] = $transaction;
                }
            }
        }


        $requested_withdrawal = \App\Models\WithdrawalRequest::where('wallet', true)->where('user_id', $user->id)->where('status', 'pending')->first();

        if(isset($transactions)) {
            return response()->json([
                'success' => true,
                'data' => $data,
                'current_page' => $transactions->currentPage(),
                'total_pages' => $transactions->lastPage(),
                'requested_withdrawal' => $requested_withdrawal ?? null,
            ]);
        } else {
            return response()->json([
                'success' => true,
                'data' => $data,
                'current_page' => 0,
                'total_pages' => 0,
                'requested_withdrawal' => $requested_withdrawal ?? null,
            ]);
        }

    }

    public function wallet_deposit(Request $request)
    {

        $helper = new AppHelper();

        $user_id = $request->input('user_id') ?? request()->user()->id ?? null;
        $amount = $request->input('amount');
        return $helper->wallet_deposit($user_id, $amount);
    }

    public function app_store(Request $request) {

        $receipt = $request->input('receipt');
        $user_id =  Auth::user()->id ?? auth()->user()->id ?? $request->input('user_id');
        $user = User::find($user_id);
        if(!$user) {
            return response([
                'success' => false,
                'errors' => 'Пользователь не найден'
            ]);
        }
        $receiptResponse = Product::appStore()->receiptData($receipt)->verifyReceipt();
        $receipt = $receiptResponse->getReceipt();
        $inAppList = $receipt->getInApp(); // contains all purchased products

        $rates = [
            '5k_coin' => 5000, '10k_coin' => 10000, '25k_coin' => 25000, '50k_coin' => 50000, '100k_coin' => 100000
        ];

        foreach ($inAppList as $inApp) {
            if($inApp->getInAppOwnershipType() == 'PURCHASED') {
                $id = $inApp->getProductId();
                $transaction_id = $inApp->getOriginalTransactionId();

                $coins = $rates[$id];

                $transaction = Payment::where('user_id', $user->id)
                    ->where('transaction_id', $transaction_id)
                    ->where('wallet_id', $user->wallet->id)
                    ->where('payment_method', 'payments')
                    ->first();
                if(!$transaction) {
                    $appstore_wallet = $user->getWallet('payments');
                    if(!$appstore_wallet) {
                        $user->createWallet([
                            'name' => 'Payments',
                            'slug' => 'payments',
                            'meta' => ['currency' => 'COINS'],
                        ]);
                    }
                    $balance = intval($appstore_wallet->balance ?? 0);
                    $user_referral = null;
                    $referral_profit = null;
                    try {
                        if($user->referral) {
                            $user_referral = $user->referral->id;
                            $referral_profit = (intval($coins) * $user->referral->donatersPercent())/100;
                            $user->referral->deposit($referral_profit, ['get' => 'referral', 'description' => 'Начисление за реферала', 'referral_id' => $user->id]);
                        }
                    } catch (\Throwable $e) {
                        Log::info([
                            'Ошибка начисления реферала payment id '.$payment->id,
                            $e->getMessage()
                        ]);
                    }
                    $appstore_wallet->deposit($coins, ['type' => 'payments', 'balance_before' => $balance, 'referral_id' => $user_referral, 'referral_profit' => $referral_profit]);
                    $payment = new Payment();
                    $payment->user_id = $user->id;
                    $payment->transaction_id = $transaction_id;
                    $payment->wallet_id = $user->wallet->id;
                    $payment->amount = $coins/100;
                    $payment->payment_method = 'appstore';
                    $payment->status = 'success';
                    $payment->save();

                    return response([
                        'success' => true,
                        'message' => 'Кошелек пополнен на '.$coins.' дилсов',
                        'balance' => $user->wallet_balance
                    ]);
                } else {
                    return response([
                        'success' => false,
                        'errors' => 'Чек уже обработан',
                        'balance' => $user->wallet_balance
                    ]);
                }

            } else {
                return response([
                    'success' => false,
                    'errors' => 'Не оплачено',
                    'balance' => $user->wallet_balance
                ]);
            }
        }


        return response([
            'success' => true,
            'balance' => $user->wallet_balance
        ]);

    }

}
