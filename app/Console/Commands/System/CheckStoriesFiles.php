<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Models\Story;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Pawlox\VideoThumbnail\VideoThumbnail;

class CheckStoriesFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stories:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $stories = Story::where('broken', false)->get();
        foreach ($stories as $story) {

            if(!$story->media) {
                continue;
            }
             if($story->media->type == 'image') {
                 $story->broken = true;
                 $story->saveQuietly();
                 echo $story->id." is broken\n";
                 continue;
            }

            $file_path = $story->media->folder ? $story->media->folder . '/' : 'uploads/stories/';
            $file_path = rtrim($file_path, '/') . '/';

            $file_name = $story->media->slug_ext;
            $videoUrl = public_path($file_path . $file_name);

            // Возможные пути и имена файлов в порядке приоритета
            $candidateFiles = [
                'dash/video_1080p_audio.mp4',
                $story->id . '/dash/video_1080p_audio.mp4',
                'dash/video_1080p.mp4',
                $story->id . '/dash/video_1080p.mp4',
            ];

            // Проверка существующих файлов
            foreach ($candidateFiles as $candidate) {
                $fullPath = $file_path . $candidate;
                $fullPublicPath = public_path($fullPath);
                if (file_exists($fullPublicPath)) {
                    $videoUrl = $fullPublicPath;
                    $file_path = dirname($fullPath); // Обновляем путь к папке
                    $file_name = basename($fullPath); // Имя файла
                    break;
                }
            }

            // Очистка путей от двойных слешей
            $videoUrl = str_replace('//', '/', $videoUrl);
            $file_path = str_replace('//', '/', $file_path);

            if(!$story->is_converted){
                $videoUrl =  public_path('images/video_processing.mp4');
                if (file_exists($videoUrl)) {
                    try {
                        \RB\HTTP\Files\Download::init(array('speed_limit' => 0, 'data_dir' => public_path('images')));
                        \RB\HTTP\Files\Download::get_file('video_processing.mp4');
                    } catch (\Throwable $e) {
                        Log::info($e->getMessage());
                    }
                }
            }

            if (file_exists($videoUrl)) {
                echo $story->id." skip\n";
                continue;
            } else {
                $story->broken = true;
                $story->saveQuietly();
                echo $story->id." is broken\n";
                continue;
            }

            if (file_exists(public_path($story->media->thumbnail))) {
                echo $story->id." skip\n";
                continue;
            } else {
                $file_path = 'uploads/stories/thumbs/story_' . $story->id . '/';
                $path = public_path($file_path);
                $file_path = 'uploads/stories/';
                if($story->media->folder) {
                    $file_path = $story->media->folder.'/';
                }

                $videoUrl = public_path($file_path . $story->media->slug_ext);
                if(file_exists("$file_path/dash/video_1080p.mp4")) {
                    $videoUrl = public_path($file_path . 'dash/video_1080p.mp4');
                }
                if(file_exists($videoUrl)) {

                } else {
                    $story->broken = true;
                    $story->saveQuietly();
                    echo $story->id." is broken\n";
                }
                if(file_exists($videoUrl)) {
                    $path = public_path($file_path);
                    if (!File::isDirectory($path)) {
                        File::makeDirectory($path, 0777, true, true);
                    }
                    $fileName = 'thumb_' . $story->media->slug . '.jpg';
                    $video_thumbnail = new VideoThumbnail();
                    $video_thumbnail->createThumbnail(
                        $videoUrl,
                        $path,
                        $fileName,
                        0,
                        $width = 607,
                        $height = 1080
                    );
                    if(file_exists(public_path($file_path . $fileName))) {
                        $story->media->thumbnail = $file_path . $fileName;
                        $story->media->saveQuietly();
                        echo $story->id." new thumbnail: \n".url($file_path . $fileName)."\n\n";
                    }
                } else {
                    $story->active = 0;
                    $story->saveQuietly();
                }
            }
        }
    }
}
