<?php

namespace App\Jobs\Cdnvideo;

use App\Models\Media;
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
class UploadMediaToCdnvideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Media $media;
    private $dirname;
    private $path;

    public function __construct(Media $media, $path = 'stories')
    {
        $this->media = $media;
        $this->path = $path;
        $this->dirname = $this->path . '/' . $this->media->user_id . '/media_' . $this->media->id;
    }

    public function handle(CdnvideoClient $client): void
    {
        if ($this->media->type != 'video') {
            return;
        }
        $dirname = null;
        try {
            $accounts = $client->get('app/inventory/v1/accounts/');
            $account_name = $accounts[0]['name'];

            $dirname = $this->dirname;

        } catch (\Throwable $e) {

        }

        try {

            try {
                $folder_exists = $client->get('app/storage/v1/' . $account_name . '/files/' . $dirname);

            } catch (\Throwable $e) {
                $folder = $client->post('app/storage/v1/' . $account_name . '/files/' . $dirname, [
                    'account_name' => $account_name,
                    'dir' => true,
                ]);
            }

            $upload = $client->post('app/storage/v1/' . $account_name . '/upload/url', [
                'url' => $this->media->path_url,
                'path' => '/' . $dirname,
                'name' => $this->media->slug_ext,
                'send_email' => false
            ]);

            GetUploadedVideo::dispatch($this->media, $dirname)->delay(10);


        } catch (\Throwable $e) {
            Log::error('Ошибка загрузки видео в UploadMediaToCdnvideo', [
                'media_id' => $this->media->id,
                'endpoint' => 'app/storage/v1/' . $account_name . '/upload/url',
                'data' => [
                    'url' => $this->media->path_url,
                    'path' => '/' . $dirname,
                    'name' => $this->media->slug_ext,
                    'send_email' => false
                ],
                'error' => $e->getMessage(),
            ]);
        }
    }
}