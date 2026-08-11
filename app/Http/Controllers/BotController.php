<?php

namespace App\Http\Controllers;

use App\Helpers\AppHelper;
use App\Helpers\ChatGPTHelper;
use App\Helpers\TgHelper;
use App\Models\Campaign;
use App\Models\Challenge;
use App\Models\Contact;
use App\Models\Story;
use App\Models\TgMessage;
use App\Models\User;
use App\Notifications\StoryNotification;
use App\Services\ProxyGuzzleHttpClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramResponseException;
use Throwable;


/**
 * Class BotController
 */
class BotController extends Controller
{
    /** @var Api */
    protected $telegram;

    use TgHelper;

    /**
     * BotController constructor.
     *
     * @param Api $telegram
     */
    public function __construct(Api $telegram, ProxyGuzzleHttpClient $proxyClient)
    {
        // Commands in app/Bot/Commands use this Api instance via CommandBus.
        // Force custom transport here to guarantee proxy usage for command replies.
        $telegram->setHttpClientHandler($proxyClient);
        $this->telegram = $telegram;
    }

    /**
     * Get updates from Telegram.
     */
    public function getUpdates()
    {
        $updates = $this->telegram->getUpdates()->getResult();
    }


    /**
     * Set a webhook.
     */
    public function setWebhook()
    {
        if (env('APP_BOT_WEBHOOK')) {
            $url = "https://".env('TG_WEBHOOK_URL', 'new.kopiberi.ru')."/bot/webhook";
            $response = $this->telegram->setWebhook(['url' => $url]);
            $updates = $this->telegram->getWebhookInfo();

            return $updates;
        }
        abort('404');
    }

    /**
     * Remove webhook.
     *
     * @return array
     */
    public function removeWebhook()
    {
        if (env('APP_BOT_WEBHOOK')) {
            $response = $this->telegram->removeWebhook();

            $updates = $this->telegram->getWebhookInfo();

            return $updates;
        }

        abort('404');

    }


    public function test()
    {
        $harold = array('CAADBAADbgADXSupAdl-C0qDP0eNAg', 'CAADBAADZgADXSupARwvmj-WrFgeAg');
        $random_harold = array_rand($harold);
        //echo $harold[$random_harold];

        try {
            $this->telegram->sendMessage(['chat_id' => '190036322', 'text' => 'Тест!']);
        } catch (TelegramResponseException $e) {
            return "user has been blocked!";
        }

    }

    public function sendTgMessage($message_id, $reply_id = null, $text, $reply_markup = null)
    {
        try {
            $this->telegram->sendMessage([
                'chat_id' => $message_id,
                'reply_to_message_id' => $reply_id,
                'text' => $text,
                'parse_mode' => 'html',
                'reply_markup' => $reply_markup,
            ]);
        } catch (TelegramResponseException $e) {
            $user = TgMessage::where('chat_id', $message_id)->first();
            if ($user) {
                $user->active = false;
                $user->save();
            }

            return "user has been blocked!";
        }

    }


    /**
     * Handles incoming webhook updates from Telegram.
     *
     * @return string
     */
    public function webhookHandler()
    {
        // получаем список зарегистрированных команд
        $commands = $this->telegram->getCommands();

        //формируем массив зарегистрированных команд
        $command_list = [];
        foreach ($commands as $name => $handler) {
            $command_list[] = '/' . $name;
        }

        try {
            $update = $this->telegram->commandsHandler(true);
        } catch (Throwable $e) {
            Log::error('webhookHandler commandsHandler error', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);
            return 'Ok';
        }

        // если сообщение - это callback_query
        if ($update->callback_query) {
            $app_helper = new AppHelper();
            $query_data = $update->callback_query->data;
            $user_id = $update->callback_query->from->id ?? $update->getMessage()->from->id;
            $user = $this->getUser($user_id, $update);
            if ($query_data == '/deposit') {
                $this->telegram->triggerCommand('deposit', $update);
            }

            $deposit_amount_query = null;

            if ($query_data == '/deposit_100') {
                $deposit_amount_query = 100;
            }
            if ($query_data == '/deposit_500') {
                $deposit_amount_query = 500;
            }
            if ($query_data == '/deposit_1000') {
                $deposit_amount_query = 1000;
            }

            if (Str::startsWith($query_data, '/suspicious_skip_') || Str::startsWith($query_data, '/suspicious_block_')) {
                $block = Str::startsWith($query_data, '/suspicious_block_');
                $accountId = (int) Str::after($query_data, $block ? '/suspicious_block_' : '/suspicious_skip_');
                $account = User::find($accountId);

                if (!$account || !$account->suspicious_moderation_pending) {
                    $reply = 'Решение по аккаунту №'.$accountId.' уже принято';
                } else {
                    if ($block) {
                        $account->active_status = 2;
                    } else {
                        $account->is_suspicious = false;
                    }
                    $account->suspicious_moderation_pending = false;
                    $account->suspicious_violations = 0;
                    $account->suspicious_moderation_requested_at = null;
                    $account->suspicious_blocked_until = null;
                    $account->save();
                    $reply = ($block ? '⛔ Аккаунт №' : '✅ Аккаунт №').$accountId
                        .($block ? ' заблокирован' : ' пропущен');
                }

                $this->sendTgMessage($user_id, null, $reply);
                $this->telegram->editMessageReplyMarkup([
                    'chat_id' => $user_id,
                    'message_id' => $update->callback_query->message->message_id,
                    'reply_markup' => null,
                ]);

                return 'Ok';
            }

            if(Str::contains($query_data, '/story_moderation_decline_')) {
                $story_id = Str::replace('/story_moderation_decline_', '', $query_data);
                $reply = '❌ Сторис #'.$story_id.' отклонена';
                $story = Story::find($story_id);
                if($story) {
                    if(!$story->moderated) {
                        $helper = new AppHelper();
                        $helper->story_decline($story, $user_id);
                    } else {
                        $reply = 'Сторис #'.$story_id.' уже отмодерирована';
                    }

                } else {
                    $reply = 'Сторис #'.$story_id.' не найдена';
                }
                $this->sendTgMessage($user_id, null, $reply, $reply_markup ?? null);
                $this->telegram->editMessageReplyMarkup(['chat_id' => $user_id, 'message_id' => $update->callback_query->message->message_id, 'reply_markup' => null]);
            }

            if(Str::contains($query_data, '/story_moderation_approve_')) {
                $story_id = Str::replace('/story_moderation_approve_', '', $query_data);
                $story = Story::find($story_id);
                $reply = '✅ Сторис #'.$story_id.' одобрена';
                if($story) {
                    if(!$story->moderated) {
                        $helper = new AppHelper();
                        $helper->story_approve($story, $user_id);
                    } else {
                        $reply = 'Сторис #'.$story_id.' уже отмодерирована';
                    }
                } else {
                    $reply = 'Сторис #'.$story_id.' не найдена';
                }
                $this->sendTgMessage($user_id, null, $reply, $reply_markup ?? null);
                $this->telegram->editMessageReplyMarkup(['chat_id' => $user_id, 'message_id' => $update->callback_query->message->message_id, 'reply_markup' => null]);
            }

            if(Str::contains($query_data, '/challenge_moderation_decline_')) {
                $challenge_id = Str::replace('/challenge_moderation_decline_', '', $query_data);
                $reply = '❌ Челлендж #'.$challenge_id.' отклонен';
                $challenge = Challenge::find($challenge_id);
                if($challenge) {
                    if(!$challenge->moderated) {
                        $helper = new AppHelper();
                        $helper->challenge_decline($challenge, $user_id);
                    } else {
                        $reply = 'Челлендж #'.$story_id.' уже отмодерирован';
                    }

                } else {
                    $reply = 'Челлендж #'.$challenge_id.' не найден';
                }
                $this->sendTgMessage($user_id, null, $reply, $reply_markup ?? null);
                $this->telegram->editMessageReplyMarkup(['chat_id' => $user_id, 'message_id' => $update->callback_query->message->message_id, 'reply_markup' => null]);
            }

            if(Str::contains($query_data, '/challenge_moderation_approve_')) {
                $challenge_id = Str::replace('/challenge_moderation_approve_', '', $query_data);
                $challenge = Challenge::find($challenge_id);
                $reply = '✅ Челлендж #'.$challenge_id.' одобрен';
                if($challenge) {
                    if(!$challenge->moderated) {
                        $helper = new AppHelper();
                        $helper->challenge_approve($challenge, $user_id);
                    } else {
                        $reply = 'Челлендж #'.$challenge_id.' уже отмодерирован';
                    }
                } else {
                    $reply = 'Челлендж #'.$challenge_id.' не найден';
                }
                $this->sendTgMessage($user_id, null, $reply, $reply_markup ?? null);
                $this->telegram->editMessageReplyMarkup(['chat_id' => $user_id, 'message_id' => $update->callback_query->message->message_id, 'reply_markup' => null]);
            }


            if(Str::contains($query_data, '/campaign_moderation_approve_')) {
                $campaign_id = Str::replace('/campaign_moderation_approve_', '', $query_data);
                $reply = '✅ Кампания #'.$campaign_id.' одобрена';
                $campaign = Campaign::find($campaign_id);
                if($campaign) {
                    if(!$campaign->moderated) {
                        $helper = new AppHelper();
                        $helper->campaign_status($campaign_id, 'approve');
                    } else {
                        $reply = 'Кампания #'.$campaign_id.' уже отмодерирована';
                    }

                } else {
                    $reply = 'Кампания #'.$campaign_id.' не найдена';
                }
                $this->sendTgMessage($user_id, null, $reply, $reply_markup ?? null);
                $this->telegram->editMessageReplyMarkup(['chat_id' => $user_id, 'message_id' => $update->callback_query->message->message_id, 'reply_markup' => null]);
            }

            if(Str::contains($query_data, '/campaign_moderation_decline_')) {
                $campaign_id = Str::replace('/campaign_moderation_decline_', '', $query_data);
                $reply = '❌ Кампания #'.$campaign_id.' отклонена';
                $campaign = Campaign::find($campaign_id);
                if($campaign) {
                    if(!$campaign->moderated) {
                        $helper = new AppHelper();
                        $helper->campaign_status($campaign_id, 'block');
                    } else {
                        $reply = 'Кампания #'.$campaign_id.' уже отмодерирована';
                    }

                } else {
                    $reply = 'Кампания #'.$campaign_id.' не найдена';
                }
                $this->sendTgMessage($user_id, null, $reply, $reply_markup ?? null);
                $this->telegram->editMessageReplyMarkup(['chat_id' => $user_id, 'message_id' => $update->callback_query->message->message_id, 'reply_markup' => null]);
            }


            if ($deposit_amount_query) {
                $data = $app_helper->wallet_deposit($user->user->id, $deposit_amount_query, true);
                if ($data['success']) {
                    $reply = "Ваша ссылка для пополпнения кошелька:\n\n" . $data['payment_url'];
                    $inline_button = array("text" => "Перейти к оплате", "url" => $data['payment_url']);
                    $inline_keyboard = [[$inline_button]];
                    $keyboard = array("inline_keyboard" => $inline_keyboard);
                    $reply_markup = json_encode($keyboard);
                } else {
                    $reply = $data['error'];
                }
                $this->sendTgMessage($user_id, null, $reply, $reply_markup ?? null);
            }


            return 'Ok';
        }

        $message = $update->getMessage();

        //если это команда, ничего не отвечаем
        try {
            if (in_array($message->text, $command_list)) {
                return 'Ok';
            }

            $chat_type = $message->chat->type ?? null;
            if ($chat_type !== 'private') {
                return 'Ok';
            }
        } catch (Throwable $e) {
            Log::info(['webhookHandler error', $e->getMessage()]);
            return 'Ok';
        }


        try {
            $user_id = $update->getMessage()->from->id;
        } catch (Throwable $e) {
            return 'Ok';
        }

        $user = $this->getUser($user_id, $update);

        $lang = 'rus';


        try {
            $this->telegram->sendChatAction(['chat_id' => $message->chat->id, 'action' => 'typing']);
        } catch (TelegramResponseException $e) {
            return "user has been blocked!";
        }

        sleep(1);

        // проверяем на наличие активной команды connect
        if ($user && $user->command == 'connect') {

            $continue = true;
            $reply_markup = null;

            $token = $message->text;

            $reply = 'Ок';
            $contact = User::where('connect_token', $token)->first();
            if ($contact) {
                if ($contact->telegram_id) {
                    $reply = 'Аккаунт Telegram уже подключен для пользователя ' . $contact->name . ' (' . $contact->email . ')';
                } else {
                    $contact->telegram_id = $message->chat->id;
                    $contact->saveQuietly();
                    $reply = 'Аккаунт Telegram успешно подключен для пользователя ' . $contact->name . ' (' . $contact->email . ')';
                }

            } else {
                $reply = 'Аккаунт не найден!';
            }

            $this->sendTgMessage($message->chat->id, $message->messageId, $reply, $reply_markup);

            return 'Ok';
        }

        // проверяем на наличие активной команды deposit
        if (($user && $user->command == 'deposit' || $user && $user->command == 'wallet')) {
            $reply_markup = null;
            if ($this->isConnected($user)) {
                $deposit_amount = intval($message->text);
                if ($deposit_amount < 50) {
                    $reply = 'Укажите 50 или больше';
                } else {
                    $app_helper = new AppHelper();
                    $data = $app_helper->wallet_deposit($user->user->id, $deposit_amount, true);

                    if ($data['success']) {
                        $reply = "Ваша ссылка для пополпнения кошелька:\n\n" . $data['payment_url'];
                        $inline_button = array("text" => "Перейти к оплате", "url" => $data['payment_url']);
                        $inline_keyboard = [[$inline_button]];
                        $keyboard = array("inline_keyboard" => $inline_keyboard);
                        $reply_markup = json_encode($keyboard);
                    } else {
                        $reply = $data['error'];
                    }
                }
            } else {
                $reply =  'Ваш телеграм-аккаунт не подключен. Используйте /connect для подключения';
            }


            $this->sendTgMessage($message->chat->id, $message->messageId, $reply, $reply_markup ?? null);

            return 'Ok';
        }

        // проверяем на наличие активной команды withdraw
        if (($user && $user->command == 'withdraw' || $user && $user->command == 'wallet')) {
            $reply_markup = null;
            if ($this->isConnected($user)) {
                $withdraw_amount = intval($message->text);
                if ($withdraw_amount < 50000) {
                    $reply = 'Укажите 50000 или больше';
                } else {
                    $app_helper = new AppHelper();
                    $data = $app_helper->wallet_withdraw_request($user->user->id, $withdraw_amount);

                    if ($data['success']) {
                        $reply = "Ваш запрос на вывод добавлен!";
                        $reply_markup = null;
                    } else {
                        $reply = $data['error'];
                    }
                }
            } else {
                $reply =  'Ваш телеграм-аккаунт не подключен. Используйте /connect для подключения';
            }


            $this->sendTgMessage($message->chat->id, $message->messageId, $reply, $reply_markup ?? null);

            return 'Ok';
        }

        $validation = $this->messageValidation($message, $this->telegram, $lang);

        if (is_array($validation)) {
            if (!$validation['success']) {
                if ($validation['sticker']) {
                    try {
                        $this->telegram->sendSticker(['chat_id' => $message->chat->id, 'sticker' => $validation['sticker']]);
                    } catch (\Throwable $e) {
                        return 'Ok';
                    }
                }
                if ($validation['text']) {

                    $this->sendTgMessage($message->chat->id, $message->messageId, $validation['text'], null);

                }
                return 'Ok';
            }
        }

        // проверяем на наличие активной команды deposit
        if (($user && $user->command == 'assist')) {
            $reply_markup = null;

            $reply = '';

            $message_data = $message->text;
            $reply = 'Простите, я бы рад пообщаться, но пока не могу :)';
            $chatgpt = new ChatGPTHelper();
            try {
                $chatgpt_response = $chatgpt->assistant_text($message_data);
                $reply = $chatgpt_response['message'];
            } catch (\Throwable $e) {
                Log::info('AssistantBotAnswer error '.$e->getMessage());
            }

            $this->sendTgMessage($message->chat->id, $message->messageId, $reply, $reply_markup ?? null);

            return 'Ok';
        }

        if ($user && $user->command == 'start') {
            $chunks = explode(' ', $update->getMessage()->text, 2);
            try {
                if (isset($chunks[1])) {
                    $token = $chunks[1];
                    $contact = User::where('connect_token', $token)->first();
                    if ($contact) {
                        if ($contact->telegram_id) {
                            $reply = 'Аккаунт Telegram уже подключен для пользователя ' . $contact->name . ' (' . $contact->email . ')';
                        } else {
                            $contact->telegram_id = $message->chat->id;
                            $contact->saveQuietly();
                            $reply = 'Аккаунт Telegram успешно подключен для пользователя ' . $contact->name . ' (' . $contact->email . ')';
                        }

                    } else {
                        $reply = 'Аккаунт Telegram не найден!';
                    }
                    $this->telegram->sendMessage(['chat_id' => $message->chat->id, 'text' => $reply]);
                }
            } catch (TelegramResponseException $e) {
                return "error";
            }

            return 'Ok';
        }

        return 'Ok';


    }
}
