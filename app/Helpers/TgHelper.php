<?php

namespace App\Helpers;

use App\Models\TgMessage;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

trait TgHelper
{

    public static function sendTgMessage($text, $user_id, $link = null)
    {
        try {
            $botToken = env('TELEGRAM_BOT_TOKEN', '6942318602:AAF7h5O00sxeshn7USEDG7ad5cpbYP0VI_g');
            $endpoint = 'https://api.telegram.org/bot' . $botToken . '/sendMessage';

            $params = [
                'chat_id' => $user_id,
                'parse_mode' => 'html',
                'text' => $text,
            ];

            if ($link) {
                $inline_button = array("text" => 'Перейти', "url" => $link);
                $inline_keyboard = [[$inline_button]];
                $keyboard = array("inline_keyboard" => $inline_keyboard);
                $params['reply_markup'] = json_encode($keyboard);
            }

            $ok = self::sendTelegramWithProxyRetry($endpoint, $params, $user_id);
            if (!$ok) {
                return "user has been blocked!";
            }
        } catch (Throwable $e) {
            Log::info($e->getMessage());
            return "user has been blocked!";
        }
    }

    private static function sendTelegramWithProxyRetry(string $endpoint, array $params, $chatId): bool
    {
        $proxyScheme = env('PROXY_SCHEME');
        $proxyAddress = env('PROXY_ADDRESS');
        $proxyPort = env('PROXY_PORT');
        $proxyLogin = env('PROXY_LOGIN');
        $proxyPassword = env('PROXY_PASSWORD');

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);

        $result = curl_exec($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $decoded = $result !== false ? json_decode($result, true) : null;

        $needProxyRetry = false;
        if ($result === false || !is_array($decoded) || (($decoded['ok'] ?? false) !== true)) {
            $needProxyRetry = true;
            Log::warning('sendTgMessage direct failed', [
                'chat_id' => $chatId,
                'error' => $curlError,
                'http_code' => $httpCode,
                'response' => $result,
            ]);
        }

        if ($needProxyRetry && !empty($proxyAddress) && !empty($proxyPort)) {
            curl_setopt($ch, CURLOPT_PROXY, $proxyAddress);
            curl_setopt($ch, CURLOPT_PROXYPORT, (int) $proxyPort);

            if (!empty($proxyLogin) || !empty($proxyPassword)) {
                curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyLogin . ':' . $proxyPassword);
            }

            switch (strtolower((string) $proxyScheme)) {
                case 'socks5':
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                    break;
                case 'socks4':
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
                    break;
                case 'http':
                case 'https':
                default:
                    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                    break;
            }

            $result = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $decoded = $result !== false ? json_decode($result, true) : null;
        }

        curl_close($ch);

        if ($result === false || !is_array($decoded) || (($decoded['ok'] ?? false) !== true)) {
            Log::error('sendTgMessage failed', [
                'chat_id' => $chatId,
                'error' => $curlError,
                'http_code' => $httpCode,
                'response' => $result,
            ]);
            return false;
        }

        return true;
    }

    public static function getUser($user_id, $update)
    {
        $user = TgMessage::where('user_id', $user_id)->first();

        if ($user) {
            if (!$user->username) {
                $user->username = $update->getMessage()->from->username;
            }
            if (!$user->firstname) {
                $user->firstname = $update->getMessage()->from->firstName;
            }
            if (!$user->lastname) {
                $user->lastname = $update->getMessage()->from->lastName;
            }
            $user->last_message = $update->getMessage();
            $user->save();
        } else {
            $user = TgMessage::create([
                'user_id' => $user_id,
                'username' => $update->getMessage()->from->username,
                'firstname' => $update->getMessage()->from->firstName,
                'lastname' => $update->getMessage()->from->lastName,
            ]);
        }

        return $user;
    }

    public function isConnected($message)
    {
        return $message->user->telegram_id ?? false;
    }

    public static function messageValidation($message, $tg, $lang)
    {

        // если прислали фото
        if ($message->photo) {

            $text_array = [
                'eng' => 'Great picture!',
                'rus' => 'Отличное изображение!',
            ];

            $reply = $text_array['rus'];

            return [
                'success' => false,
                'text' => $reply,
                'sticker' => null
            ];
        }
        // если прислали файл
        if ($message->document) {

            $text_array = [
                'eng' => "You don't have to...",
                'rus' => 'Файлы здесь не нужны...',
            ];

            $reply = $text_array['rus'];
            return [
                'success' => false,
                'text' => $reply,
                'sticker' => null
            ];
        }

        if (is_string($message->text)) {
            // реагируем на ключевые слова и отсылаем стикер
            if ($message && Str::contains($message->text, ['ура', 'привет', 'работает', 'спасибо'])) {
                return [
                    'success' => false,
                    'text' => null,
                    'sticker' => 'CAACAgQAAxkBAAIHG2GYARYlk0hpe6w0VblcpDrBueNWAAJeAANdK6kBi5oDtC-_pbsiBA'
                ];
            }
        }

        return true;
    }

}
