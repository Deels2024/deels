<?php

namespace App\Jobs\Stories;

use App\Models\Story;
use FFMpeg\Coordinate\Dimension;
use FFMpeg\FFMpeg;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Pawlox\VideoThumbnail\VideoThumbnail;

class GenerateThumbAndWatermark implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $story;

    public function __construct($story)
    {
        $this->story = $story;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $story = Story::withoutGlobalScopes()->find($this->story->id);
            if (!$story || !$story->media) {
                return;
            }
            $media = $story->media;
            $videoUrl = public_path('uploads/stories/'.date('Y/m/d').'/' . $media->slug_ext);

            $file_path = 'uploads/stories/';
            if($media->folder) {
                $file_path = $media->folder.'/';
            }

            $file_path = $file_path.'thumbs/story_' . $story->id . '/';
            $path = public_path($file_path);
            $fileName = 'thumb_' . $media->slug . '.jpg';
            if (!$media->thumbnail) {
                if (!File::isDirectory($path)) {
                    File::makeDirectory($path, 0777, true, true);
                }
                if (!file_exists($path . $fileName)) {
                    $video_thumbnail = new VideoThumbnail();
                    $video_thumbnail->createThumbnail(
                        $videoUrl,
                        $path,
                        $fileName,
                        0,
                        $width = 607,
                        $height = 1080
                    );
                }
                $media->thumbnail = $file_path . $fileName;
                $media->saveQuietly();
            }


            if ($story->challenge_id && $story->type == 'video') {
                $file_path = 'uploads/stories/';
                if($media->folder) {
                    $file_path = $media->folder.'/';
                }
                $converted_filename = 'w_' . $media->slug;
                $converted_filename_ext = 'w_' . $media->slug_ext;
                $videoUrl_converted = public_path($file_path . $converted_filename_ext);
                $watermarkPath = public_path('/images/watermark_small.png');

                $ffmpeg = FFMpeg::create([
                    'ffmpeg.binaries' => env('FFMPEG'),
                    'ffprobe.binaries' => env('FFPROBE')
                ]);

                $video = $ffmpeg->open($videoUrl);

                $dimension = $video->getStreams()->videos()->first()->getDimensions();

                // Создаем фильтры
                $filters = $video->filters();

                // Если ширина не 720, масштабируем с сохранением пропорций
                if($dimension->getWidth() != 720) {
                    $aspectRatio = $dimension->getHeight() / $dimension->getWidth();
                    $newHeight = round(720 * $aspectRatio);

                    // Сначала масштабируем
                    $filters->resize(new \FFMpeg\Coordinate\Dimension(720, $newHeight), \FFMpeg\Filters\Video\ResizeFilter::RESIZEMODE_INSET);

                    // Затем добавляем паддинг, если нужно (чтобы получить 720x405)
                    if ($newHeight < 405) {
                        $filters->pad(new \FFMpeg\Coordinate\Dimension(720, 405));
                    }
                }

                // Добавляем водяной знак
                $filters->watermark($watermarkPath, [
                    'position' => 'relative',
                    'top' => 170,
                    'right' => 100,
                ]);

                $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
                $format->setKiloBitrate(0);
                $format->setAdditionalParameters([
                    '-preset', 'slow',
                    '-crf', '18'
                ]);

                $video->save($format, $videoUrl_converted);

                $media->slug = $converted_filename;
                $media->slug_ext = $converted_filename_ext;
                $media->saveQuietly();
                File::delete($videoUrl);
            }
        } catch (\Throwable $e) {
            Log::info('Video watermark generation error (line '.$e->getLine().') '.$e->getMessage());
            Log::info($e);
        }



    }
}
