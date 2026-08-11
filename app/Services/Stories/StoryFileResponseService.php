<?php

declare(strict_types=1);

namespace App\Services\Stories;

use App\Models\Story;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\Contests\ContestVisibilityService;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class StoryFileResponseService
{
    public function getFile($id)
    {
        try {
            $story = Story::withoutGlobalScopes()->find($id);
            if (!$story) {
                return response()->json([
                    'success' => false,
                    'error' => 'Сторис не найдена',
                ]);
            }
            if (!$story->media) {
                return response()->json([
                    'success' => false,
                    'error' => 'Файл сторис не найден',
                ]);
            }
            $contest = $story->challenge_id ? $story->challenge : ($story->battle_id ? $story->battle : null);
            if ($contest && !app(ContestVisibilityService::class)->canView($contest, auth()->user())) {
                abort(404);
            }

            $filePath = $story->media->folder ? $story->media->folder . '/' : 'uploads/stories/';
            $filePath = rtrim($filePath, '/') . '/';

            $fileName = $story->media->slug_ext;
            $videoUrl = public_path($filePath . $fileName);

            foreach ($this->candidateFiles($story->id) as $candidate) {
                $fullPath = $filePath . $candidate;
                $fullPublicPath = public_path($fullPath);
                if (file_exists($fullPublicPath)) {
                    $videoUrl = $fullPublicPath;
                    $filePath = dirname($fullPath);
                    $fileName = basename($fullPath);
                    break;
                }
            }

            $videoUrl = str_replace('//', '/', $videoUrl);
            $filePath = str_replace('//', '/', $filePath);

            if (
                $story->type === 'video'
                && !$story->is_converted
                && $this->isReadyConvertedVideo($videoUrl, $fileName)
            ) {
                $story->is_converted = true;
                $story->active = true;
                $story->saveQuietly();
            }

            if ($story->type === 'video' && !$story->is_converted) {
                $processingUrl = public_path('images/video_processing.mp4');
                if (file_exists($processingUrl)) {
                    try {
                        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                        header('Pragma: no-cache');
                        header('Expires: 0');
                        \RB\HTTP\Files\Download::init(['speed_limit' => 0, 'data_dir' => public_path('images')]);
                        \RB\HTTP\Files\Download::get_file('video_processing.mp4');
                    } catch (\Throwable $e) {
                        Log::info($e->getMessage());
                    }
                }
            }

            if (!file_exists($videoUrl)) {
                return response()->json([
                    'success' => false,
                    'error' => 'Файл не найден',
                ]);
            }

            if ($story->type === 'image') {
                $response = response()->file($videoUrl, [
                    'Content-Type' => File::mimeType($videoUrl) ?: ($story->media->mime_type ?? 'image/jpeg'),
                ]);
                $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
                $response->headers->set('Pragma', 'no-cache');
                $response->headers->set('Expires', '0');

                return $response;
            }

            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Pragma: no-cache');
            header('Expires: 0');
            \RB\HTTP\Files\Download::init(['speed_limit' => 0, 'data_dir' => public_path($filePath)]);
            \RB\HTTP\Files\Download::get_file($fileName);
        } catch (\Throwable $e) {
            if ($e instanceof HttpExceptionInterface) {
                throw $e;
            }

            return response()->json([
                'success' => false,
                'error' => 'Ошибка получения файла ' . $e->getMessage(),
            ]);
        }
    }

    private function candidateFiles(int $storyId): array
    {
        return [
            'dash/video_1080p_audio.mp4',
            $storyId . '/dash/video_1080p_audio.mp4',
            'dash/video_1080p.mp4',
            $storyId . '/dash/video_1080p.mp4',
        ];
    }

    private function isReadyConvertedVideo(string $videoUrl, string $fileName): bool
    {
        if (!is_file($videoUrl) || filesize($videoUrl) === 0) {
            return false;
        }

        return Str::startsWith($fileName, ['c_', 'converted_'])
            || Str::contains(str_replace('\\', '/', $videoUrl), ['/dash/', '/hls/']);
    }
}
