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
class GetId extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'get_id';

    /**
     * @var array Command Aliases
     */
    //protected $aliases = ['listcommands'];

    /**
     * @var string Command Description
     */
    protected string $description = 'Получить ID';

    protected array $arguments = [
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
        $chat_id = 0;
        try {
            $chat_id = $update->getMessage()->chat->id;
        } catch (\Throwable $e) {

        }

        $name = $update->getMessage()->from->firstName;


        try {
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
        } catch (\Throwable $e) {

        }


        $text = 'Ваш ID: '.$user_id.' Chat ID: '.$chat_id;

        $reply_markup = null;


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
