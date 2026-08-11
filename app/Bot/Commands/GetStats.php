<?php

namespace App\Bot\Commands;

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use App\Models\TgMessage;
use Telegram\Bot\Exceptions\TelegramResponseException;

/**
 * Class HelpCommand.
 */
class GetStats extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'stats';

    /**
     * @var string Command Description
     */
    protected string $description = 'Получить статистику';

    protected array $arguments = [
    ];


    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $update = $this->getUpdate();
        $message = $update->getMessage();
        $chat_type = $message->chat->type;
        $chat_id = $update->getMessage()->chat->id;

        if(in_array($chat_id, ['-4063841845' , '4063841845', '1003906785498', '-1003906785498'])) {
            Artisan::call('deels:stats');
        }

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

    }
}
