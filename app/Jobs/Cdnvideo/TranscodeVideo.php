<?php

namespace App\Jobs\Cdnvideo;

use App\Services\Cdnvideo\CdnvideoClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

/**
 * Задача на выгрузку видеофайла на CDNvideo и запуск кодирования
 */
class TranscodeVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $media;

    public function __construct($media)
    {
        $this->media = $media;
    }

    public function handle(CdnvideoClient $client): void
    {
        $media_data = $this->media->cdn_profiles;

        try {
            $presets = [
                '67603998102ef318d5a7b081' => 'mp4 autox720p',
                '67603964102ef318d5a7b080' => 'mp4 autox480p',
                '6760393e102ef318d5a7b07f' => 'mp4 autox360p',
                '676038f5102ef318d5a7b07e' => 'mp4 autox240p',
            ];

            $accounts = $client->get('app/inventory/v1/accounts/');
            $account_name = $accounts[0]['name'];

            $data = $client->post('app/storage/v1/' . $account_name . '/transcode', [
                'object_id' => $media_data['file_id'],
                'presets' => array_keys($presets),
                'path' => '/' . $media_data['dir'],
                'delete_original' => false,
                'send_email' => false,
                'type' => 'video',
            ]);

            WarmCache::dispatch($this->media);

        } catch (\Throwable $e) {
            Log::error('Ошибка TranscodeVideo', [
                'error' => $e->getMessage(),
            ]);
        }
    }
}