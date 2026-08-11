<?php

namespace App\Console\Commands;

use App\Helpers\TgHelper;
use App\Models\Story;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Pawlox\VideoThumbnail\VideoThumbnail;

class ProcessVideo extends Command
{
    use TgHelper;
    protected $signature = 'video:process {story_id}';
    protected $description = 'Process video (compress, convert, create HLS and DASH streams)';

    private $resolutions = [
        '1080p' => ['height' => 1080, 'video_bitrate' => 5000, 'audio_bitrate' => 256],
        '720p'  => ['height' => 720,  'video_bitrate' => 2500, 'audio_bitrate' => 192],
        '480p'  => ['height' => 480,  'video_bitrate' => 1200, 'audio_bitrate' => 128],
        '360p'  => ['height' => 360,  'video_bitrate' => 800,  'audio_bitrate' => 96],
        '240p'  => ['height' => 240,  'video_bitrate' => 400,  'audio_bitrate' => 64],
    ];

    public function handle()
    {
        $story = Story::withoutGlobalScopes()->find($this->argument('story_id'));
        \App\Jobs\Stories\ProcessVideo::dispatchSync($story->id);
    }
}
