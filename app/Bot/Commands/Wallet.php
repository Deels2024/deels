<?php

namespace App\Bot\Commands;

use App\Helpers\TgHelper;
use App\Models\TgMessage;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Exceptions\TelegramResponseException;

/**
 * Class HelpCommand.
 */
class Wallet extends Command
{

    use TgHelper;
    /**
     * @var string Command Name
     */
    protected string $name = 'wallet';

    /**
     * @var array Command Aliases
     */
    //protected $aliases = ['listcommands'];

    /**
     * @var string Command Description
     */
    protected string $description = 'Кошелек';

    protected array $arguments = [
        'description' => 'Кошелек'
    ];

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $update = $this->getUpdate();

        //получаем ID сообещния
        $message_id = $update->getMessage()->messageId;
        //получаем ID пользователя
        if (!$update->getMessage()->from) {
            return 'error';
        }
        $user_id = $update->getMessage()->from->id;
        $name = $update->getMessage()->from->firstName;

        $message = TgMessage::where('user_id', $user_id)->first();
        if ($message) {
            $message->command = $this->name;
            $message->save();
        } else {
            $message = TgMessage::create([
                'user_id' => $user_id,
                'command' => $this->name,
            ]);
        }
        $message->save();

        if ($this->isConnected($message)) {
            $text_array = [
                "rus" => "Ваш баланс: ".number_format($message->user->wallet_balance ?? 0, 0, ',', ' ').' '.trans_choice('numbers.coins', $message->user->wallet->balance ?? 0),
            ];

            $text = $text_array['rus'];
            $inline_button = array("text" => "Пополнить баланс", "callback_data" => "/deposit");
            $inline_keyboard = [[$inline_button]];
            $keyboard = array("inline_keyboard" => $inline_keyboard);
            $reply_markup = json_encode($keyboard);
        } else {
            $text = 'Ваш телеграм-аккаунт не подключен. Используйте /connect для подключения';

            $reply_markup = null;
        }


        try {
            $this->replyWithMessage(['text' => $text, 'parse_mode' => 'markdown', 'disable_web_page_preview' => true,'reply_markup' => $reply_markup]);
        } catch (TelegramResponseException $e) {
            if ($message) {
                $message->active = false;
                $message->save();
            }
            return "user has been blocked!";
        }


    }
}
