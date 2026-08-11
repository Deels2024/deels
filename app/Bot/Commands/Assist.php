<?php

namespace App\Bot\Commands;

use App\Helpers\TgHelper;
use App\Models\TgMessage;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Exceptions\TelegramResponseException;

/**
 * Class HelpCommand.
 */
class Assist extends Command
{

    use TgHelper;
    /**
     * @var string Command Name
     */
    protected string $name = 'assist';

    /**
     * @var array Command Aliases
     */
    //protected $aliases = ['listcommands'];

    /**
     * @var string Command Description
     */
    protected string $description = 'Ассистент';

    protected array $arguments = [
        'description' => 'Ассистент'
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



        $text = "Чем я могу вам помочь?";

        try {
            $this->replyWithMessage(['text' => $text, 'parse_mode' => 'markdown', 'disable_web_page_preview' => true,'reply_markup' => null]);
        } catch (TelegramResponseException $e) {
            if ($message) {
                $message->active = false;
                $message->save();
            }
            return "user has been blocked!";
        }


    }
}
