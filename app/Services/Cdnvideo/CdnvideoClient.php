<?php

namespace App\Services\Cdnvideo;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Клиент для взаимодействия с API CDNvideo
 */
class CdnvideoClient
{
    private string $username;
    private string $password;

    public function __construct()
    {
        // Email и пароль из .env (добавим позже)
        $this->username = config('cdnvideo.username');
        $this->password = config('cdnvideo.password');
    }

    /**
     * Получает access_token из CDNvideo и кэширует его
     *
     * @return string
     */
    private function getAccessToken(): string
    {
        return Cache::remember('cdnvideo_access_token', now()->addMinutes(55), function () {
            $response = Http::asForm()->post('https://api.cdnvideo.ru/app/oauth/v1/token/', [
                'username' => $this->username,
                'password' => $this->password,
            ]);


            if ($response->failed()) {
                Log::error('Ошибка авторизации CDNvideo', ['response' => $response->body()]);
                throw new \Exception('Не удалось получить токен CDNvideo');
            }

            $data = $response->json();

            return $data['token'] ?? throw new \Exception('Токен отсутствует в ответе CDNvideo');
        });
    }

    /**
     * Выполняет GET-запрос к CDNvideo API
     *
     * @param string $endpoint
     * @return array
     */
    public function get(string $endpoint): array
    {
        $response = Http::withHeaders([
            'CDN-AUTH-TOKEN' => $this->getAccessToken(),
        ])->get("https://api.cdnvideo.ru/$endpoint");

        if ($response->failed()) {
//            Log::error("Ошибка запроса CDNvideo GET [$endpoint]", ['response' => $response->body()]);
            throw new \Exception("Ошибка запроса CDNvideo ".$response->body());
        }

        return $response->json();
    }

    /**
     * Выполняет POST-запрос к CDNvideo API
     *
     * @param string $endpoint
     * @param array $data
     * @return array
     */
    public function post(string $endpoint, array $data): array
    {
        $http = Http::withHeaders([
            'CDN-AUTH-TOKEN' => $this->getAccessToken(),
        ]);

        $url = "https://api.cdnvideo.ru/{$endpoint}";

        // multipart/form-data, если есть файл или флаг dir
        if (
            (isset($data['file']) && is_array($data['file'])) ||
            array_key_exists('dir', $data)
        ) {
            $multipart = [];

            // Обработка файла
            if (isset($data['file']) && is_array($data['file'])) {
                $file = $data['file'];
                $multipart[] = [
                    'name'     => 'file',
                    'contents' => fopen($file['path'], 'r'),
                    'filename' => $file['name'] ?? basename($file['path']),
                ];
                unset($data['file']);
            }


            // Добавляем остальные поля (в т.ч. 'dir' => true)
            foreach ($data as $key => $value) {
                $multipart[] = [
                    'name'     => $key,
                    'contents' => is_bool($value) ? ($value ? 'true' : 'false') : (string)$value,
                ];
            }

            // Принудительно multipart
            $response = $http->asMultipart()->post($url, $multipart);
        } else {
            // По умолчанию — JSON
            $response = $http->post($url, $data);
        }

        if ($response->failed()) {
//            Log::error("Ошибка запроса CDNvideo POST [$endpoint]", [
//                'response' => $response->body(),
//            ]);
            throw new \Exception("Ошибка запроса CDNvideo: {$response->body()} ".json_encode($data));
        }

        return $response->json();
    }

    public function delete(string $endpoint, array $data): array
    {
        $http = Http::withHeaders([
            'CDN-AUTH-TOKEN' => $this->getAccessToken(),
        ]);

        $url = "https://api.cdnvideo.ru/{$endpoint}";

        // multipart/form-data, если есть файл или флаг dir
        if (
            (isset($data['file']) && is_array($data['file'])) ||
            array_key_exists('dir', $data)
        ) {
            $multipart = [];

            // Обработка файла
            if (isset($data['file']) && is_array($data['file'])) {
                $file = $data['file'];
                $multipart[] = [
                    'name'     => 'file',
                    'contents' => fopen($file['path'], 'r'),
                    'filename' => $file['name'] ?? basename($file['path']),
                ];
                unset($data['file']);
            }


            // Добавляем остальные поля (в т.ч. 'dir' => true)
            foreach ($data as $key => $value) {
                $multipart[] = [
                    'name'     => $key,
                    'contents' => is_bool($value) ? ($value ? 'true' : 'false') : (string)$value,
                ];
            }

            // Принудительно multipart
            $response = $http->asMultipart()->delete($url, $multipart);
        } else {
            // По умолчанию — JSON
            $response = $http->post($url, $data);
        }

        if ($response->failed()) {
//            Log::error("Ошибка запроса CDNvideo POST [$endpoint]", [
//                'response' => $response->body(),
//            ]);
            throw new \Exception("Ошибка запроса CDNvideo: {$response->status()}");
        }

        return $response->json();
    }




}
