<?php

namespace App\Helpers;

use App\Jobs\FireBaseEvent;
use App\Jobs\GetStoryTags;
use App\Jobs\SendTGPMNotification;
use App\Models\Campaign;
use App\Models\Clickhouse\Action;
use App\Models\Message;
use App\Models\Order;
use App\Models\Story;
use App\Models\Thread;
use App\Models\User;
use App\Models\WithdrawalRequest;
use App\Notifications\StoryNotification;
use App\Services\Tinkoff\TinkoffEacqApi;
use Carbon\Carbon;
use Cmgmyr\Messenger\Models\Participant;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AppHelper
{

    public function telegram_message($message)
    {

        $stage = env('APP_STAGE') ?? env('APP_URL');
        try {
            $botToken = env('TELEGRAM_BOT_TOKEN', '6942318602:AAF7h5O00sxeshn7USEDG7ad5cpbYP0VI_g');

            $website = "https://api.telegram.org/bot" . $botToken;
            $chatId = 190036322;
            $message = '[' . $stage . '] ' . $message;
            $params = [
                'chat_id' => $chatId,
                'text' => $message,
            ];
            $ch = curl_init($website . '/sendMessage');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
        }
    }

    public function telegram_group_message($message, $chat_id = null, $url = null, $markup = null)
    {
        $stage = env('APP_STAGE') ?? env('APP_URL');

        try {
            $botToken = env('TELEGRAM_BOT_TOKEN');
            if (empty($botToken)) {
                Log::error('telegram_group_message: TELEGRAM_BOT_TOKEN is empty');
                return;
            }

            $website = "https://api.telegram.org/bot" . $botToken;
            $chatId = $chat_id ?: -1003906785498;

            $message = $stage . ' - ' . $message;

            $params = [
                'chat_id' => $chatId,
                'text' => $message,
            ];

            if (in_array(strtolower((string)$markup), ['markdown', 'html'])) {
                $params['parse_mode'] = ucfirst(strtolower($markup));
            }

            if ($url !== null) {
                $keyboard = [
                    'inline_keyboard' => [
                        [
                            [
                                'text' => 'Перейти к запросам',
                                'url' => $url
                            ]
                        ]
                    ]
                ];
                $params['reply_markup'] = json_encode($keyboard, JSON_UNESCAPED_UNICODE);
            }

            $endpoint = $website . '/sendMessage';

            $proxyScheme   = env('PROXY_SCHEME');
            $proxyAddress  = env('PROXY_ADDRESS');
            $proxyPort     = env('PROXY_PORT');
            $proxyLogin    = env('PROXY_LOGIN');
            $proxyPassword = env('PROXY_PASSWORD');

            $ch = curl_init($endpoint);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
            curl_setopt($ch, CURLOPT_TIMEOUT, 20);

            $result = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            $decoded = null;
            if ($result !== false) {
                $decoded = json_decode($result, true);
            }

            $needProxyRetry = false;

            if ($result === false) {
                $needProxyRetry = true;
                Log::warning('telegram_group_message direct curl error', [
                    'error' => $curlError,
                    'chat_id' => $chatId,
                    'http_code' => $httpCode,
                ]);
            } elseif (!is_array($decoded)) {
                $needProxyRetry = true;
                Log::warning('telegram_group_message direct invalid response', [
                    'response' => $result,
                    'chat_id' => $chatId,
                    'http_code' => $httpCode,
                ]);
            } elseif (($decoded['ok'] ?? false) !== true) {
                $needProxyRetry = true;
                Log::warning('telegram_group_message direct telegram error', [
                    'response' => $decoded,
                    'chat_id' => $chatId,
                    'http_code' => $httpCode,
                ]);
            }

            if (
                $needProxyRetry &&
                !empty($proxyAddress) &&
                !empty($proxyPort)
            ) {
                curl_setopt($ch, CURLOPT_PROXY, $proxyAddress);
                curl_setopt($ch, CURLOPT_PROXYPORT, (int)$proxyPort);

                if (!empty($proxyLogin) || !empty($proxyPassword)) {
                    curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyLogin . ':' . $proxyPassword);
                }

                switch (strtolower((string)$proxyScheme)) {
                    case 'socks5':
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                        break;
                    case 'socks4':
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
                        break;
                    case 'http':
                    case 'https':
                    default:
                        curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                        break;
                }

                $result = curl_exec($ch);
                $curlError = curl_error($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                $decoded = null;
                if ($result !== false) {
                    $decoded = json_decode($result, true);
                }

                if ($result === false) {
                    Log::error('telegram_group_message proxy curl error', [
                        'error' => $curlError,
                        'chat_id' => $chatId,
                        'http_code' => $httpCode,
                    ]);
                } elseif (!is_array($decoded)) {
                    Log::error('telegram_group_message proxy invalid response', [
                        'response' => $result,
                        'chat_id' => $chatId,
                        'http_code' => $httpCode,
                    ]);
                } elseif (($decoded['ok'] ?? false) !== true) {
                    Log::error('telegram_group_message proxy telegram error', [
                        'response' => $decoded,
                        'chat_id' => $chatId,
                        'http_code' => $httpCode,
                    ]);
                } else {
                    Log::info('telegram_group_message sent via proxy', [
                        'chat_id' => $chatId,
                        'http_code' => $httpCode,
                    ]);
                }
            } elseif (!$needProxyRetry) {
                Log::info('telegram_group_message sent directly', [
                    'chat_id' => $chatId,
                    'http_code' => $httpCode,
                ]);
            } else {
                Log::error('telegram_group_message failed and proxy is not configured', [
                    'chat_id' => $chatId,
                    'last_response' => $result,
                    'http_code' => $httpCode,
                ]);
            }

            curl_close($ch);
        } catch (\Throwable $e) {
            Log::error('telegram_group_message exception:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
        }
    }


    public function telegram_notify($chat_id, $message)
    {

        $stage = env('APP_STAGE') ?? env('APP_URL');
        try {
            $botToken = env('TELEGRAM_BOT_TOKEN', '6942318602:AAF7h5O00sxeshn7USEDG7ad5cpbYP0VI_g');

            $website = "https://api.telegram.org/bot" . $botToken;
            $chatId = $chat_id;
            $params = [
                'chat_id' => $chatId,
                'text' => $message,
            ];
            $ch = curl_init($website . '/sendMessage');
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, ($params));
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close($ch);
        } catch (\Throwable $e) {
        }
    }

    public function chat_notify($user, $text, $button = null)
    {
        try {
            $thread = $user->hasThread(0);
            if (!$thread) {
                $thread = \App\Models\Thread::create([
                    'subject' => Carbon::now(),
                    'users' => [0, intval($user->id)]
                ]);
                $participant = new Participant();
                $participant->thread_id = $thread->id;
                $participant->user_id = 0;
                $participant->save();

                $participant = new Participant();
                $participant->thread_id = $thread->id;
                $participant->user_id = $user->id;
                $participant->save();
            }
            $new_message = Message::create([
                'thread_id' => $thread->id,
                'user_id' => 0,
                'body' => $text,
                'button' => $button
            ]);

            if ($user->telegram_id && $user->telegram_notify) {
                $text = $text;
                $url = url('/');
                SendTGPMNotification::dispatch($user, $text, $url);
            }
        } catch (\Throwable $e) {

        }


        return true;
    }

    public function direct_chat($user, $reciever_id, $text, $button = null)
    {
        $thread = $user->hasThread($reciever_id);
        if (!$thread) {
            $thread = \App\Models\Thread::create([
                'subject' => Carbon::now(),
                'users' => [$reciever_id, intval($user->id)]
            ]);
            $participant = new Participant();
            $participant->thread_id = $thread->id;
            $participant->user_id = $reciever_id;
            $participant->save();

            $participant = new Participant();
            $participant->thread_id = $thread->id;
            $participant->user_id = $user->id;
            $participant->save();
        }
        $new_message = Message::create([
            'thread_id' => $thread->id,
            'user_id' => $user->id,
            'body' => $text,
            'button' => $button
        ]);

        return true;
    }


    public function notify_followers($user, $type = null, $model = null)
    {
        $notification = null;
        $followers = $user->followers()->with('followers')->get();
        foreach ($followers as $follower) {
            if ($type) {
                if ($type == 'campaign') {
                    $text = 'Новая копилка "' . $model->title . '" от ' . $user->name . '<br>';
                    $button = '<a href="' . route('campaign_single', $model->slug) . '" class="btn btn-small">Перейти</a>';
                    $notification = $text . $button;
                    $this->chat_notify($follower, $notification);
                }
                if ($type == 'story') {
                    $text = 'Новая сторис от ' . $user->name . '<br>';
                    $button = '<a href="' . url('') . '/stories?show=' . $model->id . '" class="btn btn-small">Перейти</a>';
                    $notification = $text . $button;
                    $this->chat_notify($follower, $notification);
                }

                if ($type == 'challenge') {
                    $text = 'Новый челлендж от ' . $user->name . '<br>';
                    $button = '<a href="' . url('') . '/challenges/show/' . $model->id . '" class="btn btn-small">Перейти</a>';
                    $notification = $text . $button;
                    $this->chat_notify($follower, $notification);
                }

                if ($notification) {
                    try {
                        Mail::send(
                            [],
                            [],
                            function (\Illuminate\Mail\Message $message) use ($follower, $notification): void {
                                $message
                                    ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
                                    ->to($follower->email)
                                    ->subject('Новое событие!')
                                    ->html($notification);
                            }
                        );
                    } catch (\Throwable $e) {

                    }
                }
            }
        }
    }

    public function firebase_notify($user_id, $message, $thread = null)
    {
        $url = 'https://fcm.googleapis.com/fcm/send';
        $FcmToken = User::where('id', $user_id)->whereNotNull('device_key')->pluck('device_key')->all();

        $serverKey = env('FIREBASE_SERVER_KEY');

        $messages_count = 0;
        $unread_messages = Thread::forUserWithNewMessages($user_id)->latest('updated_at')->pluck('messages_count')->toArray();
        foreach ($unread_messages as $unread_message) {
            $messages_count = $messages_count + $unread_message;
        }
        $data = [
            "registration_ids" => $FcmToken,
            "notification" => [
                "type" => 'message',
                "from" => [
                    'user_id' => $message->user->id,
                    'name' => $message->user->name,
                    'avatar' => $message->user->avatar(),
                ],
                "thread_id" => $message->thread_id,
                "message" => $message->body,
                "unread_messages_count" => $unread_messages,
                "unread_threads_count" => Thread::forUserWithNewMessages($user_id)->latest('updated_at')->count(),
            ]
        ];
        $encodedData = json_encode($data);

        $headers = [
            'Authorization:key=' . $serverKey,
            'Content-Type: application/json',
        ];

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $encodedData);
        // Execute post
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('Curl failed: ' . curl_error($ch));
        }
        // Close connection
        curl_close($ch);
        // FCM response
        return true;
    }

    public function wallet_deposit($user_id, $amount, $array = false)
    {
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
                    . '?Fail=${Success}&ErrorCode=${ErrorCode}&OrderId=${OrderId}&Message=${Message}&Details=${Details}',
            ];

            $res = TinkoffEacqApi::getPaymentURL($payment, $items, $options);

            $paymentURL = $res['paymentURL'];
            $tinkoff = $res['client'];

            if (!$paymentURL) {

                if ($array) {
                    return [
                        'success' => false,
                        'error' => 'Произошла ошибка. Обратитесь к администрации!'
                    ];
                }
                return response()->json([
                    'success' => false,
                    'error' => 'Произошла ошибка. Обратитесь к администрации!'
                ]);
            } else {
                $payment_id = $tinkoff->payment_id;
                $new_order->payment_id = $payment_id;
                $new_order->payment_url = $paymentURL;
                $new_order->save();

                if ($array) {
                    return [
                        'success' => true,
                        'payment_url' => $paymentURL
                    ];
                }
                return response()->json([
                    'success' => true,
                    'payment_url' => $paymentURL
                ]);
            }
        }
    }

    public function wallet_withdraw_request($user_id, $amount)
    {
        $user = User::find($user_id);
        $requested_withdrawal = WithdrawalRequest::where('wallet', true)->where('user_id', $user_id)->where('status', 'pending')->first();
        if ($requested_withdrawal) {
            $data = [
                'success' => false,
                'error' => 'Запрос уже обрабатывается'
            ];
            return $data;
        }

        if ($amount > $user->wallet_balance) {
            $data = [
                'success' => false,
                'error' => 'Недостаточно дилсов'
            ];
            return $data;
        }

        try {
            $user->wallet_withdraw($amount);
        } catch (\Throwable $e) {
            $data = [
                'success' => false,
                'errors' => $e->getMessage()
            ];
            return $data;
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
        ];
        WithdrawalRequest::create($data);
        try {
            Mail::raw(
                'Пользователь ' . $user->name . '(' . $user->email . ") запросил вывод средств на сумму $amount дилсов..",
                function (\Illuminate\Mail\Message $message): void {
                    $message
                        ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
                        ->to(env('MAIL_FROM_ADDRESS', 'info@deels.ru'))
                        ->subject('Запрос на вывод средств');
                }
            );
        } catch (\Throwable $e) {

        }

        $data = [
            'success' => true,
            'amount' => $amount,
        ];
        return $data;
    }

    public function story_approve($story, $moderated = false)
    {
        $story_user = $story->user;
        $story->active = 1;
        $story->declined = 0;
        if ($moderated) {
            $story->moderated = $moderated;
        }
        $story->save();
        GetStoryTags::dispatch($story);
        if ($story_user) {
            $helper = new AppHelper();
            $text = 'Ваша сторис прошла модерацию';
            $helper->chat_notify($story->user, $text, null);
            $story_user->notify(new StoryNotification('Ваша сторис прошла модерацию'));
        }
        $this->notify_followers($story->user, 'story', $story);
        if ($story->challenge_id) {
            FireBaseEvent::dispatch($story->user_id, 'Ваш ответ к челленджу был опубликован! ', $story->id, 'story');
        } else {
            FireBaseEvent::dispatch($story->user_id, 'Ваша сторис была опубликована!', $story->id, 'story');
        }

    }

    public function story_decline($story, $moderated = false)
    {
        $story_user = $story->user;
        $story->declined = 1;
        $story->active = 0;
        if ($moderated) {
            $story->moderated = $moderated;
        }
        $story->save();
        if ($story_user) {

            $helper = new AppHelper();
            $text = 'Ваша сторис не прошла модерацию';
            $helper->chat_notify($story->user, $text, null);
            $story_user->notify(new StoryNotification('Ваша сторис не прошла модерацию'));
        }
    }

    public function challenge_approve($challenge, $moderated = false)
    {
        $wasActive = (bool) $challenge->active;
        $challenge_user = $challenge->user;
        $challenge->active = 1;
        $challenge->declined = 0;
        $challenge->blocked_at = null;
        if ($moderated) {
            $challenge->moderated = $moderated;
        }
        $challenge->save();
        if ($challenge_user) {
            $text = 'Ваш челлендж прошел модерацию';
            $helper = new AppHelper();
            $helper->chat_notify($challenge_user->user, $text, null);
            $challenge_user->notify(new StoryNotification('Ваш челлендж прошел модерацию'));
        }
        $this->notify_followers($challenge->user, 'challenge', $challenge);
        FireBaseEvent::dispatch($challenge->user_id, 'Ваш челлендж был опубликован!', $challenge->id, 'challenge');
        if (!$wasActive) {
            app(\App\Services\Contests\ContestNotificationService::class)->challengePublished($challenge);
        }
    }

    public function challenge_decline($challenge, $moderated = false)
    {
        $challenge_user = $challenge->user;
//        $challenge->update([
//            'declined' => true,
//            'active' => false,
//            'moderated' => $moderated
//        ]);
        $challenge->declined = true;
        $challenge->active = false;
        $challenge->moderated = $moderated;
        $challenge->saveQuietly();
        if ($challenge_user) {
            $text = 'Ваш челлендж не прошел модерацию';
            $helper = new AppHelper();
            $helper->chat_notify($challenge_user->user, $text, null);
            $challenge_user->notify(new StoryNotification('Ваш челлендж не прошел модерацию'));
        }
        if (!$challenge->finished) {
            $payments_wallet = $challenge->user->getWallet('payments');
            $balance = intval($payments_wallet->balance ?? 0);
            $payments_wallet->deposit(intval($challenge->amount), ['get' => 'coins', 'balance_before' => $balance, 'description' => 'Возврат за челлендж "' . $challenge->title . '"']);
        }
    }

    public function challenge_restart($challenge, $moderated = true)
    {
        $challenge_user = $challenge->user;
        $challenge->active = 1;
        if ($challenge->min_participants > 0) {
            $challenge->started = 0;
        } else {
            $challenge->started = 1;
        }

        $challenge->finished = 0;
        $challenge->declined = 0;
        $challenge->blocked_at = null;
        $challenge->finished_at = null;

        $days = $challenge->days;
        $finish_date = Carbon::now()->addDays($days);
        $challenge->finish = $finish_date;
        $challenge->save();
        if ($challenge_user) {
            $text = 'Ваш челлендж повторно запущен';
            $helper = new AppHelper();
            $helper->chat_notify($challenge_user->user, $text, null);
            $challenge_user->notify(new StoryNotification('Ваш челлендж повторно запущен'));
        }
        Story::where('challenge_id', $challenge->id)->delete();
        DB::table('challenge_user')->where('challenge_id', $challenge->id)->delete();
        $this->notify_followers($challenge->user, 'challenge', $challenge);
        FireBaseEvent::dispatch($challenge->user_id, 'Ваш челлендж повторно запущен!', $challenge->id, 'challenge');
    }

    public function campaign_status($campaign_id, $status, $reason = null)
    {
        $campaign = Campaign::find($campaign_id);
        try {
            $campaign->moderated = Auth::user() ? Auth::user()->is_campaign_admin() : true;
        } catch (\Throwable $e) {
            $campaign->moderated = true;
        }


        if ($campaign && $status) {
            if ('approve' == $status) {
                $isActiveAuthor = $campaign->user?->isActiveAuthor() ?? false;
                $campaign->status = $isActiveAuthor ? 1 : Campaign::STATUS_SLEEPING;
                $campaign->saveQuietly();

                try {
                    Mail::send(
                        [],
                        [],
                        function (\Illuminate\Mail\Message $message) use ($campaign, $isActiveAuthor): void {
                            $html = $isActiveAuthor
                                ? "Ваша копилка $campaign->title успешно прошла модерацию. Переходите на https://deels.ru и копите на мечту прямо сейчас"
                                : "Ваша копилка $campaign->title успешно прошла модерацию, но пока спит из-за отсутствия активности автора. Переходите на https://deels.ru, опубликуйте сторис или пригласите друзей, чтобы разбудить копилку";

                            $message
                                ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
                                ->to($campaign->user->email)
                                ->subject('Успешная модерация на DEELS')
                                ->html($html);
                        }
                    );
                } catch (\Throwable $e) {

                }

                if ($isActiveAuthor) {
                    $helper = new AppHelper();
                    $helper->notify_followers($campaign->user, 'campaign', $campaign);
                    FireBaseEvent::dispatch($campaign->user_id, 'Ваша копилка была опубликована!', $campaign->id, 'campaign');
                }
            } elseif ('block' == $status) {
                $campaign->status = 2;
                $campaign->saveQuietly();
                try {
                    Mail::send(
                        [],
                        [],
                        function (\Illuminate\Mail\Message $message) use ($campaign, $reason): void {
                            $html_message = "Ваша копилка $campaign->title не прошла модерацию";
                            if ($reason && $reason != '' && $reason != ' ') {
                                $html_message .= ", Причина: $reason";
                            }
                            $html_message .= ". Переходите на https://deels.ru, исправьте данные и копите на мечту прямо сейчас";
                            $message
                                ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
                                ->to($campaign->user->email)
                                ->subject('Модерация на DEELS')
                                ->html($html_message);
                        }
                    );
                } catch (\Throwable $e) {

                }

            } elseif ('funded' == $status) {
                $campaign->is_funded = 1;
                $campaign->save();
            } elseif ('add_staff_picks' == $status) {
                $campaign->is_staff_picks = 1;
                $campaign->save();
            } elseif ('remove_staff_picks' == $status) {
                $campaign->is_staff_picks = 0;
                $campaign->save();
            } elseif ('feature' == $status) {
                $campaign->update(['is_feature' => 1]);
            }
        }
    }

    public function save_action($type, $user_id, $model)
    {
        $model_name = class_basename($model);
        $tags = [];
        if ($model_name == 'Story') {
            $tags = $model->tags()->pluck('title')->toArray();
        }
        $data = [
            'user_id' => $user_id,
            'type' => $type,
            'model' => $model_name,
            'tags' => $tags,
            'title' => $model->title ?? null,
            'description' => $model->title ?? null,
            'model_id' => $model->id,
            'created_at' => Carbon::now()
        ];

        try {
            Action::create($data);
        } catch (\Throwable $e) {
        }

    }

    public function send_socket($to, $message)
    {

        $host = env('RATCHET_HOST') ? env('RATCHET_HOST') : 'ws://localhost';
        $port = env('RATCHET_PORT') ? env('RATCHET_PORT') : 8090;
        \Ratchet\Client\connect($host . ':' . $port)->then(function ($conn) use ($message, $to) {
            $conn->on('message', function ($msg) use ($conn) {
                echo "Received: {$msg}\n";
                $conn->close();
            });

            $conn->send('{"command":"message","to":"' . $to . '","message":{' . $message . '}}');
        }, function ($e) {
            echo "Could not connect: {$e->getMessage()}\n";
        });

        return response([
            'success' => true,
        ]);
    }

    public function twitch_status($arr = false)
    {
        $clientId = env('TWITCH_CLIENT_ID');
        $clientSecret = env('TWITCH_SECRET');
        $tokenUrl = "https://id.twitch.tv/oauth2/token";

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $tokenUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'grant_type' => 'client_credentials'
        ]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);
        $accessToken = $data['access_token'] ?? null;

        if ($accessToken === null) {
            if ($arr) {
                return 'error';
            }
            return response([
                'success' => false,
            ]);
        }

        $channelId = env('TWITCH_CHANNEL');
        $baseUrl = "https://api.twitch.tv/helix/streams";

        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Client-Id: ' . $clientId
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $baseUrl . "?user_login=" . $channelId);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode($response, true);

        $isOnline = !empty($data['data']);

        if ($isOnline) {
            if ($arr) {
                return 'online';
            }
            return response([
                'success' => true,
                'status' => 'online'
            ]);
        } else {
            if ($arr) {
                return 'offline';
            }
            return response([
                'success' => true,
                'status' => 'offline'
            ]);
        }
    }

    public function write_log($ip, $type = null, $description = null)
    {
        DB::table('logs')->insert(['ip' => $ip, 'type' => $type, 'description' => $description, 'created_at' => Carbon::now()]);
    }

}
