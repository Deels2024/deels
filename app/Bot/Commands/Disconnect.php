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
class Disconnect extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'disconnect';

    /**
     * @var array Command Aliases
     */
    //protected $aliases = ['listcommands'];

    /**
     * @var string Command Description
     */
    protected string $description = 'Отключить аккаунт';

    protected array $arguments = [
        'description' => 'Отключить аккаунт'
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

        $user = User::where('telegram_id', $user_id)->first();
        if($user) {
            $user->telegram_id = null;
            $user->save();
            $text_array = [
                "rus" => 'Аккаунт Telegram успешно отключен для пользователя ' . $user->name . ' (' . $user->email . ')'
            ];
        } else {
            $text_array = [
                "rus" => 'У вас нет подключенного аккаунта'
            ];
        }


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
