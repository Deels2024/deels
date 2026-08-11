<?php

namespace App\Bot\Commands;

use App\Models\User;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Commands\Command;
use App\Models\TgMessage;
use Throwable;

/**
 * Class HelpCommand.
 */
class Connect extends Command
{
    /**
     * @var string Command Name
     */
    protected string $name = 'connect';

    /**
     * @var array Command Aliases
     */
    //protected $aliases = ['listcommands'];

    /**
     * @var string Command Description
     */
    protected string $description = 'Подключить аккаунт';

    protected array $arguments = [
        'description' => 'Подключить аккаунт'
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
            "rus" => "Для подключения аккаунта telegram к учетной записи сайта перейдите в профиль по ссылке: ".route('profile_settings')." и пришлите код для подключения. \n\nВы так же можете нажать на кнопку подключения в настройках вашего профиля на сайте.",
        ];

        $inline_button = array("text" => "Перейти в профиль", "url" => route('profile_settings'));
        $inline_keyboard = [[$inline_button]];
        $keyboard = array("inline_keyboard" => $inline_keyboard);
        $reply_markup = json_encode($keyboard);

        $text = $text_array['rus'];


        if(isset($message->user) && $message->user->telegram_id) {
            $text = 'Ваш аккаунт уже  подключен для пользователя ' . $message->user->name . ' (' . $message->user->email . ')';
            $reply_markup = null;
        }

        try {
            $this->replyWithMessage(['text' => $text, 'parse_mode' => 'html', 'disable_web_page_preview' => true, 'reply_markup' => $reply_markup]);
        } catch (Throwable $e) {
            Log::warning('ConnectCommand send error', ['message' => $e->getMessage()]);
            if ($message) {
                $message->active = false;
                $message->save();
            }
            return "user has been blocked!";
        }


    }
}
