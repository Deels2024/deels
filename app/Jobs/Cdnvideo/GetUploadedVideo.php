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
class GetUploadedVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private Media $media;
    private $dirname;

    public function __construct(Media $media, $dirname)
    {
        $this->media = $media;
        $this->dirname = $dirname;
    }

    public function handle(CdnvideoClient $client): void
    {

        $dirname = $this->dirname;
        $filename = $this->media->slug_ext;

        try {

            $accounts = $client->get('app/inventory/v1/accounts/');
            $account_name = $accounts[0]['name'];


            $uploaded_file = $client->get('app/storage/v1/' . $account_name . '/files/' . $dirname . '/' . $filename);


            $hlsUrl = $uploaded_file['data']['hls_url'] ?? null;
            if ($hlsUrl) {
                $this->media->update([
                    'hls_url' => $hlsUrl,
                    'cdn_profiles' => ['file_id' => $uploaded_file['data']['id'], 'dir' => $dirname, 'filename' => $filename],
                ]);
//                Log::info('success hls url for ID'.$this->media->id);
                TranscodeVideo::dispatch($this->media);
            } else {
                self::dispatch($this->media, $this->dirname)->delay(30);
            }


        } catch (\Throwable $e) {
            self::dispatch($this->media, $this->dirname)->delay(30);
            Log::error('Ошибка получения файла GetUploadedVideo', [
                'media_id' => $this->media->id,
                'dir' => '/files/' . $dirname . '/' . $filename,
                'error' => $e->getMessage(),
            ]);
        }
    }
}