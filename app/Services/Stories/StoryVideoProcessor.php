<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Services\UserService;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Pawlox\VideoThumbnail\VideoThumbnail;

class StoryVideoProcessor
{
    public function uploadCover($coverFile, $story): void
    {
        try {
            $userService = new UserService();
            $file = $userService->uploadCover($coverFile, $story->id);
            $story->cover = $file;
            $story->save();
        } catch (\Throwable $e) {
            Log::error('Ошибка загрузки обложки: ' . $e->getMessage());
        }
    }

    public function process($story, $paid): void
    {
        $media = $story->media;
        $videoUrl = public_path($media->path);

        if (file_exists($videoUrl)) {
            $this->generateThumbnail($videoUrl, $media, $story->id);
            // Deprecated function. App\Jobs\Stories\ProcessVideo
            // $this->convertVideoToPortrait($videoUrl, $media);
        }

        if ($paid) {
            $this->generateVideoPreview($media);
        }
    }

    private function generateThumbnail($videoUrl, $media, $storyId): void
    {
        $media_file_path = 'uploads/stories/';
        if ($media->folder) {
            $media_file_path = $media->folder;
        }
        $file_path = $media_file_path . '/thumbs/story_' . $storyId . '/';
        $path = public_path($file_path);
        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0777, true, true);
        }
        $fileName = 'thumb_' . $media->slug . '.jpg';
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
        $media->save();
    }

    private function convertVideoToPortrait($videoUrl, $media): void
    {
        $converted_filename = 'c_' . $media->slug;
        $converted_filename_ext = 'c_' . $media->slug_ext;
        $file_path = 'uploads/stories/';
        if ($media->folder) {
            $file_path = $media->folder . '/';
        }
        $videoUrl_converted = public_path($file_path . $converted_filename_ext);

        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => env('FFMPEG'),
            'ffprobe.binaries' => env('FFPROBE')
        ]);
        $video = $ffmpeg->open($videoUrl);

        $landscapeWidth = $video->getStreams()->first()->get('width');
        $landscapeHeight = $video->getStreams()->first()->get('height');

        if ($landscapeWidth > $landscapeHeight || $landscapeWidth == $landscapeHeight || ($landscapeHeight / $landscapeWidth) < 1.7) {
            try {
                $portraitWidth = 720;
                $portraitHeight = 1280;
                $video->filters()
                    ->pad(new \FFMpeg\Coordinate\Dimension($portraitWidth, $portraitHeight))
                    ->synchronize();
                $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
                $format->setKiloBitrate(0);
                $format->setAdditionalParameters([
                    '-preset', 'slow',       // Лучшее сжатие без потери качества
                    '-crf', '22',           // Оптимальное значение (18-23, где 23 - хороший баланс)
                    '-pix_fmt', 'yuv420p',  // Совместимость со всеми устройствами
                    '-movflags', '+faststart', // Для потокового воспроизведения
                    '-vf', 'scale=trunc(iw/2)*2:trunc(ih/2)*2', // Четные размеры (обход ошибок кодирования)
                ]);

                $video->save($format, $videoUrl_converted);
                $media->slug = $converted_filename;
                $media->slug_ext = $converted_filename_ext;
                $media->save();
                File::delete($videoUrl);
            } catch (\Throwable $e) {
                Log::error('Ошибка ресайза видео: ' . $e->getMessage());
            }
        }
    }

    private function generateVideoPreview($media): void
    {
        try {
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => env('FFMPEG'),
                'ffprobe.binaries' => env('FFPROBE')
            ]);
            $file_path = 'uploads/stories/';
            if ($media->folder) {
                $file_path = $media->folder;
            }
            $video_preview_path = $file_path . 'preview_' . $media->slug_ext;
            $media_video = $file_path . $media->slug_ext;
            $video_preview = public_path($video_preview_path);
            $saved_video = $ffmpeg->open($media_video);
            $start = TimeCode::fromSeconds(0);
            $duration = TimeCode::fromSeconds(3);
            $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
            $format->setKiloBitrate(20000);
            $format->setAdditionalParameters([
                '-preset', 'slow',       // Лучшее сжатие без потери качества
                '-crf', '22',           // Оптимальное значение (18-23, где 23 - хороший баланс)
                '-pix_fmt', 'yuv420p',  // Совместимость со всеми устройствами
                '-movflags', '+faststart', // Для потокового воспроизведения
                '-vf', 'scale=trunc(iw/2)*2:trunc(ih/2)*2', // Четные размеры (обход ошибок кодирования)
            ]);
            $saved_video->clip($start, $duration)->save($format, $video_preview);
            $media->video_preview = $video_preview_path;
            $media->save();
        } catch (\Throwable $e) {
            Log::error('Ошибка генерации превью видео: ' . $e->getMessage());
        }
    }
}
