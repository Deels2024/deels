<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Models\Campaign;
use App\Models\Order;
use App\Models\Payment;
use App\Models\WithdrawalRequest;
use App\User;
use Bavix\Wallet\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Mail\Message;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $title = trans('app.payments');

        $payments = Payment::query();
        $transactions = Transaction::query()
            ->where('amount', '<', 0)
            ->where('type', 'withdraw');
//            ->where('meta', 'like', '%"donate":"story"%');
        if (!$user->is_admin()) {
            $campaign_ids = $user->my_campaigns()->pluck('id')->toArray();
            $payments = $payments->whereIn('campaign_id', $campaign_ids);
        }

        $query_user = null;
        if ($request->email) {
//            $payments = $payments->where('email', 'like', "%{$request->q}%");
            $payments = $payments->where('email', $request->email);
            $query_user = \App\Models\User::where('email', $request->email)->first();
        }
        if ($request->date_from) {
            $payments = $payments->where('created_at', '>=', $request->date_from);
            $transactions->where('created_at', '>=', $request->date_from);
        }
        if ($request->date_to) {
            $payments = $payments->where('created_at', '<=', $request->date_to);
            $transactions->where('created_at', '<=', $request->date_to);
        }
        if ($request->company_search) {
            $payments = $payments->where('campaign_id', $request->company_search);
        }
        if (request()?->has('excel')) {
            return $this->collectionToExcel($payments->orderBy('id', 'desc')->get());
        }

        if($query_user) {
            $transactions->where('payable_type', 'App\Models\User')->where('payable_id', $query_user->id);
        }

        $transactions = $transactions->orderBy('id', 'desc')->limit(200)->get();

        $payments = $payments->orderBy('id', 'desc')->limit(200)->get();

        $payments = $payments->merge($transactions)->sortByDesc('created_at');
//        $payments = collect($payments)->groupBy('created_at');

        return view('admin.payments', compact('title', 'payments'));
    }

    public function view($id)
    {
        $title = trans('app.payment_details');
        $payment = Payment::find($id);

        return view('admin.payment_view', compact('title', 'payment'));
    }

    public function withdraw()
    {
        $user = Auth::user();
        $title = trans('app.withdraw');
        $campaigns = $user->my_campaigns;
        $withdrawal_requests = WithdrawalRequest::whereUserId($user->id)->orderBy('id', 'desc')->get();

        return view('admin.withdraw', compact('title', 'campaigns', 'withdrawal_requests'));
    }

    /** @return \Illuminate\Http\RedirectResponse */
    public function withdrawRequest(Request $request)
    {
        $user_id = Auth::user()->id;
        $campaign_id = $request->withdrawal_campaign_id;

        if ($request->get('code') !== $request->session()->get('code')) {
            return redirect()->back()->with('error', 'Invalid Code');
        }

        $requested_withdrawal = WithdrawalRequest::whereCampaignId($campaign_id)->first();
        if ($requested_withdrawal) {
            return redirect()->back()->with('error', trans('app.this_withdraw_is_processing'));
        }

        $withdrawal_preference = withdrawal_preference();
        if (!$withdrawal_preference) {
            return redirect()->back()->with('error', trans('app.update_withdrawal_preference_info'));
        }

        $campaign = Campaign::find($campaign_id);
        $withdraw_amount = $campaign->amount_raised()->campaign_owner_commission;
        $total_amount = $campaign->amount_raised()->amount_raised;
        $platform_owner_commission = $campaign->amount_raised()->platform_owner_commission;

        if ($total_amount < 1) {
            return redirect()->back()->with('error', trans('app.invalid_withdrawal_amount'));
        }

        $data = [
            'user_id' => $user_id,
            'campaign_id' => $campaign_id,
            'total_amount' => $total_amount,
            'platform_owner_commission' => $platform_owner_commission,
            'withdrawal_amount' => $withdraw_amount,
            'withdrawal_account' => $withdrawal_preference,
            'status' => 'pending',
        ];

        if ('paypal' == $withdrawal_preference) {
            $data['paypal_email'] = withdrawal_preference('paypal_email');
        } elseif ('bank' == $withdrawal_preference) {
            $data['bank_account_holders_name'] = withdrawal_preference('bank_account_holders_name');
            $data['bank_account_number'] = withdrawal_preference('bank_account_number');
            $data['swift_code'] = withdrawal_preference('swift_code');
            $data['bank_name_full'] = withdrawal_preference('bank_name_full');
            $data['bank_branch_name'] = withdrawal_preference('bank_branch_name');
            $data['bank_branch_city'] = withdrawal_preference('bank_branch_city');
            $data['bank_branch_address'] = withdrawal_preference('bank_branch_address');
            $data['country_id'] = withdrawal_preference('country_id');
        }

        WithdrawalRequest::create($data);
        Mail::raw(
            'Пользователь ' . Auth::user()->name . '(' . Auth::user()->email . ") запросил вывод средств на сумму $total_amount р..",
            function (Message $message): void {
                $message
                    ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
                    ->to(env('MAIL_FROM_ADDRESS', 'info@deels.ru'))
                    ->subject('Запрос на вывод средств');
            }
        );

        return redirect()->back()->with('success', trans('app.withdraw_request_sent'));
    }

    /**
     * @param $id
     *
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function withdrawRequestView($id)
    {
        $title = trans('app.withdrawal_details');
        $withdraw_request = WithdrawalRequest::find($id);
        $user = Auth::user();

        if (!$user->is_admin()) {
            if ($user->id != $withdraw_request->user_id) {
                exit('Unauthorize request');
            }
        }

        return view('admin.withdrawal_details', compact('title', 'withdraw_request'));
    }

    public function withdrawalRequests()
    {
        $title = trans('app.withdrawal_requests');
        $withdraw_requests = WithdrawalRequest::whereHas('user')->orderBy('id', 'desc')->paginate(20);

        return view('admin.withdraw_requests', compact('title', 'withdraw_requests'));
    }

    public function withdrawalRequestsStatusSwitch(Request $request, $id = 0)
    {
        $user = Auth::user();
        if (!$user->is_admin()) {
            return redirect()->back()->with('error', trans('app.unauthorised_access'));
        }

        $withdraw_request = WithdrawalRequest::find($id);
        $withdraw_request->status = $request->type;
        $withdraw_request->save();

        return redirect()->back()->with('success', trans('app.withdrawal_request_status_changed'));
    }

    /**
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     *
     * @date  April 29, 2017
     *
     * @since v.1.1
     */
    public function paymentsPending(Request $request)
    {
        $user = Auth::user();
        $title = trans('app.payments');

        if ($user->is_admin()) {
            if ($request->q) {
                $payments = Payment::pending()->where('email', 'like', "%{$request->q}%")->orderBy('id', 'desc')->paginate(20);
            } else {
                $payments = Payment::pending()->orderBy('id', 'desc')->paginate(20);
            }
        } else {
            $campaign_ids = $user->my_campaigns()->pluck('id')->toArray();
            if ($request->q) {
                $payments = Payment::pending()
                    ->whereIn('campaign_id', $campaign_ids)
                    ->where('email', 'like', "%{$request->q}%")
                    ->orderBy('id', 'desc')
                    ->paginate(20);
            } else {
                $payments = Payment::pending()->whereIn('campaign_id', $campaign_ids)->orderBy('id', 'desc')->paginate(20);
            }
        }

        return view('admin.payments', compact('title', 'payments'));
    }

    public function markSuccess($id, $status)
    {
        $payment = Payment::find($id);
        $payment->status = $status;
        $payment->save();

        return back()->with('success', trans('app.payment_status_changed'));
    }

    public function backedCampaigns()
    {
        $user = Auth::user();
        $title = trans('app.backed_campaigns');
        $payments = $user->backed_payments()->paginate(20);

        return view('admin.backed_campaigns', compact('title', 'user', 'payments'));
    }

    public function autopayments()
    {
        $payments = Payment::where('user_id', Auth::id())
            ->whereNotNull('rebill_id')
            ->get()
            ->keyBy('campaign_id');

        $campaigns = Campaign::whereIn('id', $payments->pluck('campaign_id'))
            ->get()
            ->each(function (Campaign $campaign) use ($payments): void {
                /**
                 * @var Payment[] $payments
                 */
                $date = Carbon::make(date($payments[$campaign->id]->created_at->format('d') . '.m.Y'));
                if ($date->isPast()) {
                    $date = $date->addMonth();
                }
                $campaign->autopayAmount = $payments[$campaign->id]->amount;
                $campaign->autopayDate = $date;
            });

        return view('admin.autopayments', compact('campaigns'));
    }

    public function deleteAutopayment(int $campaignId)
    {
        $payments = Payment::where('user_id', Auth::id())
            ->where('campaign_id', $campaignId)
            ->whereNotNull('rebill_id')
            ->update(['rebill_id' => null]);

        return back();
    }

    public function callbackPayment(\Illuminate\Http\Request $request)
    {
        $data = $request->all();
        try {
            if(!empty($data)) {
                Log::info(['callbackPayment', $data]);
            }

        } catch (\Throwable $e) {
            Log::info('callbackPayment log error');
        }
        if(isset($data['PaymentId'])) {
            $order = Order::where('payment_id', $data['PaymentId'])->first();
            if ($order) {
                $this->checkOrder($data, $order);
            }
        }

        return 'OK';

    }

    public function checkOrder($data, $order)
    {
        if ($order) {
            /** @var Order $order */
            $model = $order->model;

            if ($data['Status'] == 'AUTHORIZED') {
                $order->rebill_id = $data['RebillId'] ?? null;
            }
            if ($data['Status'] == 'CONFIRMED') {
                if($order->status != 1) {
                    if ($model == 'App\Models\User' && $order->type == 'wallet') {
                        $user = \App\Models\User::find($order->model_id);
                        if ($user && ($data['Status'] == 'CONFIRMED')) {
                            $appstore_wallet = $user->getWallet('payments');
                            if(!$appstore_wallet) {
                                $user->createWallet([
                                    'name' => 'Payments',
                                    'slug' => 'payments',
                                    'meta' => ['currency' => 'COINS'],
                                ]);
                            }
                            $coins = ($order->amount*100);
                            $balance = intval($appstore_wallet->balance ?? 0);
                            $user_referral = null;
                            try {
                                if($user->referral) {
                                    $user_referral = $user->referral->id;
                                    $referral_profit = (intval($coins) * $user->referral->donatersPercent())/100;
                                    $user->referral->deposit($referral_profit, ['get' => 'referral', 'description' => 'Начисление за реферала', 'referral_id' => $user->id]);
                                }
                            } catch (\Throwable $e) {
                                Log::info([
                                    'Ошибка начисления реферала заказ ID'.$order->id,
                                    $e->getMessage()
                                ]);
                            }

                            $appstore_wallet->deposit(intval($coins), ['type' => 'payments', 'balance_before' => $balance, 'referral_id' => $user_referral, 'referral_profit' => $referral_profit ?? 0]);


                            if($user->telegram_id) {
                                $app_helper = new AppHelper();
                                $deposit_amount = intval($order->amount*100);
                                $app_helper->telegram_notify($user->telegram_id, 'Баланс успешно пополнент на '.$deposit_amount.' '.trans_choice('numbers.coins', $deposit_amount ?? 0));
                            }
                        }
                    }
                    $order->status = 1;
                }
            }
            if ($data['Status'] == 'CANCELED' || $data['Status'] == 'REFUNDED') {
                $order->status = 2;
            }

            $order->save();
        }
    }

    public function successPage(Request $request) {
        $title = trans('app.payment_success');

        return view('success_payment_tinkoff', compact('title'));
    }

}
