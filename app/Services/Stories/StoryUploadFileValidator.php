<?php

declare(strict_types=1);

namespace App\Services\Stories;

use FFMpeg\FFProbe;
use Illuminate\Support\Facades\Log;

class StoryUploadFileValidator
{
    private const STORY_IMAGE_FORMAT_ERROR = 'Этот формат не поддерживается. Пожалуйста, загрузите фото в формате JPEG, JPG, PNG, HEIF или HEIC';
    private const STORY_VIDEO_FORMAT_ERROR = 'Используйте видео формата mp4, mov или avi';
    private const STORY_VIDEO_DURATION_ERROR = 'Длительность видео превышает 60 сек. Пожалуйста, загрузите более короткое видео для сторис';

    public function validate($file): ?string
    {
        if ($this->isImageFile($file)) {
            return $this->validateImageFile($file);
        }

        $maxMb = (int) config('media.stories.max_upload_mb');
        if ($file->getSize() > $maxMb * 1024 * 1024) {
            return "Вес файла превышает {$maxMb} Мб. Пожалуйста, загрузите файл до {$maxMb} Мб";
        }

        if (!$this->isVideoFile($file)) {
            return self::STORY_VIDEO_FORMAT_ERROR;
        }

        if ($this->videoDurationExceedsLimit($file)) {
            return self::STORY_VIDEO_DURATION_ERROR;
        }

        return null;
    }

    private function validateImageFile($file): ?string
    {
        $extension = strtolower($file->getClientOriginalExtension());

        if (in_array($extension, config('media.stories.image_unsupported_extensions', []), true)) {
            return self::STORY_IMAGE_FORMAT_ERROR;
        }

        if (!in_array($extension, config('media.stories.image_allowed_extensions', []), true)) {
            return self::STORY_IMAGE_FORMAT_ERROR;
        }

        $maxMb = (int) config('media.stories.image_max_upload_mb');
        if ($file->getSize() > $maxMb * 1024 * 1024) {
            return "Вес файла превышает {$maxMb} Мб. Пожалуйста, загрузите файл до {$maxMb} Мб";
        }

        return null;
    }

    private function isImageFile($file): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        $mimeType = $file->getClientMimeType();

        return substr($mimeType, 0, 5) === 'image'
            || in_array($extension, config('media.stories.image_allowed_extensions', []), true)
            || in_array($extension, config('media.stories.image_unsupported_extensions', []), true);
    }

    private function isVideoFile($file): bool
    {
        return in_array($file->getClientMimeType(), config('media.stories.video_allowed_mime_types', []), true);
    }

    private function videoDurationExceedsLimit($file): bool
    {
        try {
            $ffprobe = FFProbe::create([
                'ffmpeg.binaries' => env('FFMPEG'),
                'ffprobe.binaries' => env('FFPROBE')
            ]);

            $duration = (float) $ffprobe->format($file->getPathname())->get('duration');

            return $duration > (int) config('media.stories.max_video_duration_seconds');
        } catch (\Throwable $e) {
            Log::error('Ошибка проверки длительности видео сторис: ' . $e->getMessage());
            return false;
        }
    }
}
