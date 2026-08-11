<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Jobs\GetStoryTags;
use App\Models\Payment;
use App\Models\Story;
use App\Models\Thanks;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class AddVideoPreviewsStories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stories:previews:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add video previews to current stories';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stories = Story::orderBy('created_at', 'DESC')->where('active', true)->where('paid', true)->whereHas('media', function($q): void {
            $q->where('mime_type', 'like', '%video%')->whereNull('video_preview');
        })->get();

        echo "Getting ".count($stories)."\n";
        foreach ($stories as $story){
            $ffmpeg = FFMpeg::create([
                'ffmpeg.binaries' => env('FFMPEG'),
                'ffprobe.binaries' => env('FFPROBE')
            ]);
            $media = $story->media;
            try {
                $video_preview_path = 'uploads/stories/' . 'preview_' . $media->slug_ext;
                $media_video = 'uploads/stories/'.$media->slug_ext;
                $video_preview = public_path($video_preview_path);
                $saved_video = $ffmpeg->open(public_path($media_video));
                $start = TimeCode::fromSeconds(0);
                $duration = TimeCode::fromSeconds(3);
                $format = new \FFMpeg\Format\Video\X264('aac', 'libx264');
                $format->setKiloBitrate(20000);
                $format->setAdditionalParameters([
                    '-preset', 'slow',
                    '-crf', '18'
                ]);
//                $saved_video->clip($start, $duration)->save(new \FFMpeg\Format\Video\X264(), $video_preview);
                $saved_video->clip($start, $duration)->save($format, $video_preview);
                $media->video_preview = $video_preview_path;
                $media->save();
            } catch (\Throwable $e) {
                echo $e->getMessage()."\n";
            }
        }
    }
}
