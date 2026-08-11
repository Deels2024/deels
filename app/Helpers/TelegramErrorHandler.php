<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class TelegramErrorHandler
{
    public static function send(Throwable $exception)
    {
        $APP_DEV_MODE = env('APP_DEV_MODE');

        if (config('error_tracking_telegram.telegram_bot_token') && !$APP_DEV_MODE) {
            if (!Str::contains($exception->getMessage(), 'Another route has already been assigned name')) {
                $name = 'Ошибка ' . env('APP_NAME');
                $botToken = env('TELEGRAM_BOT_TOKEN', '6942318602:AAF7h5O00sxeshn7USEDG7ad5cpbYP0VI_g');
                $chatId = env('TELEGRAM_CHAT_ID', 190036322);
                $website = 'https://api.telegram.org/bot' . $botToken;

                $message = self::jsonToText([
                    'Message' => $exception->getMessage(),
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'url' => url()->full(),
                ]);

                $sent = self::sendTelegramWithProxyRetry($website . '/sendMessage', [
                    'chat_id' => $chatId,
                    'text' => "<b>{$name}</b>\n{$message}",
                    'parse_mode' => 'HTML',
                ], $chatId);

                if ($sent && Str::contains($exception->getMessage(), 'Permission denied')) {
                    $traceContent = "Exception: " . $exception->getMessage() . "\n";
                    $traceContent .= "File: " . $exception->getFile() . "\n";
                    $traceContent .= "Line: " . $exception->getLine() . "\n";
                    $traceContent .= "URL: " . url()->full() . "\n\n";
                    $traceContent .= "Stack Trace:\n" . $exception->getTraceAsString();

                    $fileName = 'trace_' . date('Y-m-d_H-i-s') . '.txt';
                    $tempFilePath = sys_get_temp_dir() . '/' . $fileName;
                    file_put_contents($tempFilePath, $traceContent);

                    self::sendTelegramWithProxyRetry($website . '/sendDocument', [
                        'chat_id' => $chatId,
                        'document' => curl_file_create($tempFilePath),
                        'caption' => 'Full stack trace for Permission denied error',
                    ], $chatId);

                    unlink($tempFilePath);
                }
            }
        }
    }

    public static function jsonToText($message)
    {
        $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        $message = str_replace(['<', '>'], ['&lt;', '&gt;'], $message);
        $message = self::traitMessage(json_decode($message, true));

        if (mb_strlen($message) > 4000) {
            $message = mb_substr($message, 0, 4000) . '...';
        }

        return $message;
    }

    public static function traitMessage($arr)
    {
        $text = '';

        foreach ($arr as $key => $value) {
            if (is_array($value) == false) {
                $text .= "{$key}: {$value}\n";
            } else {
                $text .= "\n{$key}:\n" . self::traitMessage($value) . "\n";
            }
        }

        return $text;
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
//            Log::warning('TelegramErrorHandler direct send failed', [
//                'chat_id' => $chatId,
//                'error' => $curlError,
//                'http_code' => $httpCode,
//                'response' => $result,
//            ]);
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
            Log::error('TelegramErrorHandler send failed', [
                'chat_id' => $chatId,
                'error' => $curlError,
                'http_code' => $httpCode,
                'response' => $result,
            ]);
            return false;
        }

        return true;
    }
}
