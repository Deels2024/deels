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
class Deposit extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'deposit';

    /**
     * @var array Command Aliases
     */
    //protected $aliases = ['listcommands'];

    /**
     * @var string Command Description
     */
    protected string $description = 'Пополнить баланс';

    protected array $arguments = [
        'description' => 'Пополнить баланс'
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
        $text_array = [
            "rus" => "Выберите сумму для пополнения или укажите свое значение:",
        ];

        $inline_button = array("text" => "100 ₽", "callback_data" => "/deposit_100");
        $inline_button2 = array("text" => "500 ₽", "callback_data" => "/deposit_500");
        $inline_button3 = array("text" => "1000 ₽", "callback_data" => "/deposit_1000");
        $inline_keyboard = [[$inline_button, $inline_button2, $inline_button3]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply_markup = json_encode($keyboard);

        $text = $text_array['rus'];


        try {
            $this->replyWithMessage(['text' => $text, 'parse_mode' => 'markdown', 'disable_web_page_preview' => true, 'reply_markup' => $reply_markup]);
        } catch (TelegramResponseException $e) {
            if ($message) {
                $message->active = false;
                $message->save();
            }
            return "user has been blocked!";
        }


    }
}
