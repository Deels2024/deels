<?php

namespace App\Bot\Commands;

use App\Models\TgMessage;
use Telegram\Bot\Commands\Command;
use Telegram\Bot\Exceptions\TelegramResponseException;

/**
 * Class HelpCommand.
 */
class HelpCommand extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'help';

    /**
     * @var array Command Aliases
     */
    //protected $aliases = ['listcommands'];

    /**
     * @var string Command Description
     */
    protected string $description = '';

    protected array $arguments = [
        'description' => 'Список команд'
    ];

    /**
     * {@inheritdoc}
     */
    public function handle()
    {
        $commands = $this->telegram->getCommands();
        $update = $this->getUpdate();

        //получаем ID сообещния
        $message_id = $update->getMessage()->messageId;
        //получаем ID пользователя
        $user_id = $update->getMessage()->from->id ?? $update->getMessage()->chat->id ?? null;
        if (!$user_id) {
            return "error";
        }
        $name = $update->getMessage()->from->firstName ?? null;

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

        $text = '';
        foreach ($commands as $name => $command) {
            if (!in_array($name, ['help', 'start', 'get_id'])) {
                $command_arguments = $command->getArguments();
                if (array_key_exists('description', $command_arguments)) {
                    $text .= sprintf('/%s - %s' . PHP_EOL, $name, $command->getArguments()['description']);
                }

            }
        }
        try {
            $this->replyWithMessage(compact('text'));
        } catch (TelegramResponseException $e) {
            if ($message) {
                $message->active = false;
                $message->save();
            }
            return "user has been blocked!";
        }


    }
}
