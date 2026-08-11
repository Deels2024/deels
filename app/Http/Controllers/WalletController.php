<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Faq;
use App\Models\Order;
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

class WalletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $user = request()->user();
        $type = $request->input('type');
        $withdrawals = [];
        if($type && $type == 'billing') {
            $payments_wallet = $user->getWallet('payments');
            $transactions = Transaction::where('wallet_id', $payments_wallet->id)->where('meta', 'like', '%Пополнение%')->orderBy('created_at', 'DESC')->get();
//            $withdrawals = \App\Models\WithdrawalRequest::where('wallet', true)->where('user_id', $user->id)->get();
//            $transactions = $transactions->merge($withdrawals)->sortByDesc('created_at');
        }  elseif ($type && $type == 'donate') {
            $wallet = $user->getWallet('default');
            $transactions = Transaction::where('wallet_id', $user->wallet->id)->where('amount', '>', 0)->orderBy('created_at', 'DESC')->get();
        }
            else {
            $payments_wallet = $user->getWallet('payments');
            $payments_wallet_transactions = Transaction::where('wallet_id', $payments_wallet->id)->whereNot('meta', 'like', '%Пополнение%')->orderBy('created_at', 'DESC')->get();

            $transactions = Transaction::where('wallet_id', $user->wallet->id)->whereNotNull('meta')->orderBy('created_at', 'DESC')->paginate(10);
            $transactions = $payments_wallet_transactions->merge($transactions);


        }

//        $appstore_wallet = $user->getWallet('appstore');
//        $user_wallet = $user->getWallet('default');
//        $appstore_wallet->exchange($user_wallet, 10);
//        dd($appstore_wallet->balance, $user_wallet->balance);

        return view('dashboard.wallet.wallet_index', compact('user', 'transactions', 'withdrawals'));
    }

    public function wallet_deposit(Request $request)
    {

        $user_id = $request->input('user_id');
        $amount = $request->input('amount');
        $amount = preg_replace("/\s+/", "", $amount);
        $amount = intval($amount);
        $user = User::find($user_id);
        if ($user) {

            $order_id = Carbon::now()->format('YmdHis');

            $new_order = new Order([
                'user_id' => $user->id,
                'model' => User::class,
                'model_id' => $user->id,
                'amount' => $amount ?? 0,
                'order_id' => $order_id,
                'type' => 'wallet',
                'status' => $amount > 0 ? 0 : 1,
            ]);

            $description = 'Пополнение кошелька';
            $redirect_url = route('user_wallet');

            $new_order->save();

            $payment = [
                'OrderId' => env('TINKOFF_ORDER_PREFIX', '') . $order_id,
                'Amount' => $new_order->amount,
                'Language' => 'ru',
                'Description' => $description,
                'Email' => $user->email,
                'Phone' => null,
                'Name' => $user->name,
                'Taxation' => 'usn_income_outcome',
            ];
            $items[] = [
                'Name' => $description,
                'Price' => $new_order->amount,
                'NDS' => 'none',
            ];
            $options = [
                'NotificationURL' => route('cb-payment'),
                'SuccessURL' => $redirect_url
                    . '?Success=${Success}&ErrorCode=${ErrorCode}&OrderId=${OrderId}&Message=${Message}&Details=${Details}',
                'FailURL' => $redirect_url
                    . '?Success=${Success}&ErrorCode=${ErrorCode}&OrderId=${OrderId}&Message=${Message}&Details=${Details}',
            ];

            $res = TinkoffEacqApi::getPaymentURL($payment, $items, $options);

            $paymentURL = $res['paymentURL'];
            $tinkoff = $res['client'];

            if (!$paymentURL) {
                return back()->with('error', 'Произошла ошибка. Обратитесь к администрации!');
            } else {
                $payment_id = $tinkoff->payment_id;
                $new_order->payment_id = $payment_id;
                $new_order->payment_url = $paymentURL;
                $new_order->save();
                Redirect::to($paymentURL)->send();
            }
        }
    }

    public function wallet_withdraw(Request $request)
    {
        $user_id = $request->input('user_id');
        $amount = $request->input('amount');
        $user = User::find($user_id);
        if ($user) {
            try {
                $user->manual_withdraw($amount,['donate' => 'withdraw', 'description' => 'Вывод средств']);
            } catch (\Throwable $e) {
                return back()->with('error', $e->getMessage());
            }

        }
        if($amount < 500) {
            return back()->with('Минимальная сумма для вывода: 500 рублей');
        }
        return back()->with('success', 'Средства выведены!');
    }

    public function withdrawWalletRequest(Request $request)
    {
        $user_id = $request->input('user_id') ?? request()->user()->id ?? null;
        $amount = $request->input('amount');
        $contacts = $request->input('contacts');


        $user = User::find($user_id);
        if($amount < 500) {
            return response()->json([
                'success' => false,
                'errors' => 'Минимальная сумма для вывода: 500 рублей'
            ]);
        }
        if(!$contacts) {
            return response()->json([
                'success' => false,
                'errors' => 'Укажите контакты для связи'
            ]);
        } else {
            $user->contacts = $contacts;
            $user->saveQuietly();
        }

        $rules['fio'] = 'required';
        $rules['phone'] = 'required';
        $rules['schet'] = 'required';
        $rules['bik'] = 'required';
        $rules['bank'] = 'required';
        $rules['korr'] = 'required';
        $rules['inn'] = 'required';
        $rules['kpp'] = 'required';
        $validator = validator($request->all(), $rules);
        if($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => 'Заполните все поля реквизитов'
            ]);
        }

        if (Auth::user() && Auth::user()->id != $user_id) {
            return response()->json([
                'success' => false,
                'errors' => 'Запрос невозможен'
            ]);
        }

        $requested_withdrawal = WithdrawalRequest::where('wallet', true)->where('user_id', $user_id)->where('status', 'pending')->first();
        if ($requested_withdrawal) {
            return response()->json([
                'success' => false,
                'errors' => 'Запрос уже обрабатывается'
            ]);
        }

        $lat_requested_withdrawal = WithdrawalRequest::where('wallet', true)->where('user_id', $user_id)->orderBy('created_at', 'DESC')->first();
        if($lat_requested_withdrawal) {
            $now = Carbon::now();
            $proxy_ends = \Carbon\Carbon::parse($lat_requested_withdrawal->created_at);
            $diff = $now->diffInDays($proxy_ends);
            $left = 30 - $diff;
            if($diff < 30) {
                return response()->json([
                    'success' => false,
                    'errors' => 'Вывод доступен раз в 30 дней. Повторите запрос через '.$left.' '.trans_choice('numbers.days', $left)
                ]);
            }
        }

        if ($amount > $user->withdraw_balance) {
            return response()->json([
                'success' => false,
                'errors' => 'Недостаточно средств'
            ]);
        }


        try {
            $user->manual_withdraw($amount,['donate' => 'withdraw', 'description' => 'Вывод средств']);
        } catch (\Throwable $e) {

            return response()->json([
                'success' => false,
                'errors' => $e->getMessage()
            ]);
        }


        $data = [
            'user_id' => $user_id,
            'campaign_id' => null,
            'total_amount' => $amount,
            'platform_owner_commission' => 0,
            'withdrawal_amount' => $amount,
            'withdrawal_account' => $user_id,
            'status' => 'pending',
            'wallet' => true,
            'data' => null,
        ];
        WithdrawalRequest::create($data);
        try {
            Mail::raw(
                'Пользователь ' . Auth::user()->name . '(' . Auth::user()->email . ") запросил вывод средств на сумму $amount рублей..",
                function (Message $message): void {
                    $message
                        ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
                        ->to(env('MAIL_FROM_ADDRESS', 'info@deels.ru'))
                        ->subject('Запрос на вывод средств');
                }
            );

            Mail::raw(
                'Заявка на вывод средств успешно отправлена. Ожидайте поступление на карту в течение 21 дня.',
                function (Message $message): void {
                    $message
                        ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
                        ->to(env('MAIL_FROM_ADDRESS', 'info@deels.ru'))
                        ->subject('Заявка на вывод средств');
                }
            );

            $message = "🔔*Заявка на вывод*:\n";
            $message .= "\nФИО: ".$request->input("fio");
            $message .= "\nНомер телефона: ".$request->input("phone");
            $message .= "\nНомер счета: ".$request->input("schet");
            $message .= "\nБИК: ".$request->input("bik");
            $message .= "\nБанк-получатель: ".$request->input("bank");
            $message .= "\nКорр. счет: ".$request->input("korr");
            $message .= "\nИНН банка: ".$request->input("inn");
            $message .= "\nКПП: ".$request->input("kpp");
            $message .= "\nКонтакт для связи: ".$contacts;
            $message .= "\n\n*Сумма: ".$amount." ₽*";
            $telegram = new AppHelper();
            $telegram->telegram_group_message($message, env('WITHDRAW_CHAT_ID'), route('withdrawal_requests'), 'markdown');

            Log::info(['withdrawal_requests', env('WITHDRAW_CHAT_ID'), $message]);
        } catch (\Throwable $e) {
            Log::info('wallet_withdraw error: '.$e->getMessage());
        }


        return response()->json([
            'success' => true,
            'amount' => $amount,
        ]);

    }

    public function withdrawalRequestsConfirmation(Request $request)
    {
        $type = $request->input('type');
        $withdraw_id = $request->input('withdraw');
        $requested_withdrawal = WithdrawalRequest::find($withdraw_id);
        if (!$requested_withdrawal) {
            return redirect()->back()->with('error', 'Запрос не найден');
        }

        $requested_withdrawal->status = $type;
        $requested_withdrawal->save();

        if ($type == 'declined') {
            try {
                $balance = intval($requested_withdrawal->user->wallet->balance ?? 0);
                $requested_withdrawal->user->wallet->deposit(intval($requested_withdrawal->withdrawal_amount)*100,['get' => 'coins', 'balance_before' => $balance, 'description' => 'Возврат вывода']);
            } catch (\Throwable $e) {
                return back()->with('error', $e->getMessage());
            }
        }

        return back()->with('success', 'Действие успешно');
    }


}
