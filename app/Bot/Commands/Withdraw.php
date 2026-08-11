<?php

namespace App\Bot\Commands;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use App\Models\TgMessage;
use Telegram\Bot\Exceptions\TelegramResponseException;

/**
 * Class HelpCommand.
 */
class Withdraw extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'withdraw';

    /**
     * @var array Command Aliases
     */
    //protected $aliases = ['listcommands'];

    /**
     * @var string Command Description
     */
    protected string $description = 'Вывести с баланса';

    protected array $arguments = [
        'description' => 'Вывести с баланса'
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

        $balance = number_format($message->user->wallet->balance ?? 0, 0, ',', ' ');
        $text_array = [
            "rus" => "Ваш баланс: $balance\n\nУкажите сумму для вывода.\nМинимальная сумма для вывода: 50000 дилсов.",
        ];

        $inline_button = array("text" => "100 ₽", "callback_data" => "/deposit_100");
        $inline_button2 = array("text" => "500 ₽", "callback_data" => "/deposit_500");
        $inline_button3 = array("text" => "1000 ₽", "callback_data" => "/deposit_1000");
        $inline_keyboard = [[$inline_button, $inline_button2, $inline_button3]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply_markup = null;

        $text = $text_array['rus'];


        try {
            $this->replyWithMessage(['text' => $text, 'parse_mode' => 'markdown', 'disable_web_page_preview' => true, 'reply_markup' => null]);
        } catch (TelegramResponseException $e) {
            if ($message) {
                $message->active = false;
                $message->save();
            }
            return "user has been blocked!";
        }


    }
}
