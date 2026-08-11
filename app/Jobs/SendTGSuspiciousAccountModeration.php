<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Telegram\Bot\Api;
use Throwable;

class SendTGSuspiciousAccountModeration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private int $userId) {}

    public function handle(): void
    {
        $user = User::find($this->userId);
        if (! $user || ! $user->suspicious_moderation_pending) {
            return;
        }

        $moderators = array_values(array_filter(preg_split(
            '/\r\n|\r|\n/',
            (string) get_option('tg_moderators', true)
        ) ?: []));

        if ($moderators === []) {
            Log::warning('Suspicious account moderation notification has no Telegram recipients', [
                'user_id' => $user->id,
            ]);

            return;
        }

        $profileUrl = route('user.profile', $user->id);
        $text = sprintf(
            'Аккаунт <a href="%s">№%d</a> выглядит подозрительным',
            e($profileUrl),
            $user->id
        );
        $keyboard = json_encode([
            'inline_keyboard' => [[
                ['text' => 'Пропустить', 'callback_data' => '/suspicious_skip_'.$user->id],
                ['text' => 'Заблокировать', 'callback_data' => '/suspicious_block_'.$user->id],
            ]],
        ], JSON_UNESCAPED_UNICODE);

        $telegram = new Api((string) env('TELEGRAM_BOT_TOKEN'));
        foreach ($moderators as $moderator) {
            try {
                $telegram->sendMessage([
                    'chat_id' => $moderator,
                    'parse_mode' => 'html',
                    'reply_markup' => $keyboard,
                    'text' => $text,
                ]);
            } catch (Throwable $exception) {
                Log::warning('Unable to notify suspicious account moderator', [
                    'user_id' => $user->id,
                    'moderator' => $moderator,
                    'error' => $exception->getMessage(),
                ]);
            }
        }
    }
}
