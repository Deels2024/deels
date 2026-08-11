<?php

namespace App\Jobs\Cdnvideo;

use App\Services\Cdnvideo\CdnvideoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Задача на выгрузку видеофайла на CDNvideo и запуск кодирования
 */
class WarmCache implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $media;

    public function __construct($media)
    {
        $this->media = $media;
    }

    public function handle(CdnvideoClient $client): void
    {
        try {
            $media_data = $this->media->cdn_profiles;

            $accounts = $client->get('app/inventory/v1/accounts/');
            $account_name = $accounts[0]['name'];
            $uploaded_file = $client->get('app/storage/v1/' . $account_name . '/files/' . $media_data['dir'] . '/' . $media_data['filename']);
            $url_components = parse_url($uploaded_file['data']['hls_url']);
            $domain = $url_components['host'];
            $path = $url_components['path'];
            $task_data = [
                'domain' => $domain,
                'action' => 'preload',
                'paths' => [
                    $path
                ]
            ];

            $task = $client->post('app/cache/v3/' . $account_name . '/tasks', $task_data);

            if (!isset($task['task_id'])) {
                self::dispatch($this->media)->delay(now()->addSeconds(10));
            }

        } catch (\Throwable $e) {
            self::dispatch($this->media)->delay(now()->addSeconds(15));
            if(!Str::contains($e->getMessage(), 'Too Many Requests')) {
                Log::error('Ошибка WarmCache', [
                    'media' => $media_data,
                    'error' => $e->getMessage(),
                ]);
            }

        }
    }
}