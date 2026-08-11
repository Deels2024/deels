<?php

namespace App\Bot\Commands;

use App\Models\Contact;
use App\Models\TgMessage;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use Throwable;

/**
 * Class StartCommand
 */
class StartCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'start';

    /**
     * @var array Command Aliases
     */
    //protected $aliases = ['signup'];

    /**
     * @var string Command Description
     */
    protected string $description = '';

    protected array $arguments = [
        'description' => 'Старт'
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
        $user_id = $update->getMessage()->from->id ?? null;
        if (!$user_id) {
            return "error";
        }
        $name = $update->getMessage()->from->firstName ?? null;

        $message = TgMessage::where('user_id', $user_id)->first();
        if ($message) {
            $message->command = $this->name;
            $message->active = true;
            $message->save();
        } else {
            $message = TgMessage::create([
                'user_id' => $user_id,
                'command' => $this->name,
                'username' => $update->getMessage()->from->username ?? null,
                'firstname' => $update->getMessage()->from->firstName ?? null,
                'lastname' => $update->getMessage()->from->lastName ?? null,
            ]);
            $username = $update->getMessage()->from->username ?? 'channel';
        }

        $name = $update->getMessage()->from->firstName ?? null;
        if ($name) {
            $name = ', ' . $name;
        }

        $text_array = [
            "rus" => "Добрый день" . $name . "!\nЭто бот для сайта " . env('APP_URL', 'https://deels.ru'),
        ];



        $text = $text_array['rus'];

        if(!isset($message->user) || isset($message->user) && !$message->user->telegram_id) {
            $text .= "\n\nИспользуйте команду /connect, чтобы подключить аккаунт";
        }
        try {
            $this->replyWithMessage(['text' => $text, 'disable_web_page_preview' => true]);
        } catch (Throwable $e) {
            Log::warning('StartCommand send error', ['message' => $e->getMessage()]);
            if ($message) {
                $message->active = false;
                $message->save();
            }
            return "user has been blocked!";
        }

    }
}
