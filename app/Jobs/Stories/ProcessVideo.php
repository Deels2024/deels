<?php

namespace App\Jobs\Stories;

use App\Helpers\TgHelper;
use App\Models\Story;
use FFMpeg\FFMpeg;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Pawlox\VideoThumbnail\VideoThumbnail;

class ProcessVideo implements ShouldQueue
{
    use TgHelper;
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    public $timeout = 7200;
    public $tries = 3;
    public $maxExceptions = 2;

    private $story_id;
    private $fast;

    private $resolutions = [
        '1080p' => ['height' => 1080, 'video_bitrate' => 5000, 'audio_bitrate' => 256],
        '720p' => ['height' => 720, 'video_bitrate' => 2500, 'audio_bitrate' => 192],
        '480p' => ['height' => 480, 'video_bitrate' => 1200, 'audio_bitrate' => 128],
        '360p' => ['height' => 360, 'video_bitrate' => 800, 'audio_bitrate' => 96],
        '240p' => ['height' => 240, 'video_bitrate' => 400, 'audio_bitrate' => 64],
    ];

    public function __construct($story_id, $fast = false)
    {
        $this->story_id = $story_id;
        $this->fast = $fast;
    }


    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Battle stories are excluded by Story's legacy global scope.
        // Media processing must be able to load stories attached to any contest.
        $story = Story::withoutGlobalScopes()->find($this->story_id);
        if(!$story) {
            return 1;
        }
        if ($story->type !== 'video') {
            $story->is_converted = true;
            $story->saveQuietly();
            return 0;
        }
        $story->active = 0;
        $story->saveQuietly();
        $media = $story->media;
        $inputPath = public_path($story->getFile(true));
        $originalFile = $inputPath;
        $baseMediaFolder = $media->folder ?: 'uploads/stories';
        $media_path = $baseMediaFolder . '/' . $story->id . '/';
        $media_path = str_replace('//', '/', $media_path);
        $inputPath = str_replace('//', '/', $inputPath);

        $storagePath = public_path($media_path);
        $watermarkPath = public_path('/images/watermark_small.png');
//        $this->sendTgMessage('ProcessVideo processed ' . $story->id . ' media id ' . $media->id, 190036322);
        if (!file_exists($inputPath)) {
            $this->sendTgMessage('Input file does not exist '.$story->id, 190036322);
            $this->sendTgMessage('file not exists ' . $story->id . ' ' . $inputPath, 190036322);
            return 1;
        }

        if (!file_exists($storagePath)) {
            mkdir($storagePath, 0755, true);
        }

        $ffmpegPath = env('FFMPEG', '/usr/bin/ffmpeg');
        $ffprobePath = env('FFPROBE', '/usr/bin/ffprobe');

        // Analyze video
        $ffprobeCmd = "$ffprobePath -v error -select_streams v:0 -show_entries stream=width,height,codec_name -of csv=p=0:s=x \"$inputPath\"";
        $videoInfo = trim(shell_exec($ffprobeCmd));

        // Обработка разных форматов вывода
        $parts = explode("x", $videoInfo);

        if (count($parts) === 3) {
            // Формат: widthxheightxcodec
            [$width, $height, $codec] = $parts;
        } elseif (count($parts) === 4) {
            // Формат: codecxwidthxheightx (как в случае с HEVC)
            $codec = $parts[0];
            $width = $parts[1];
            $height = $parts[2];
        } else {
            throw new \Exception("Неизвестный формат вывода ".$story->id." ffprobe: " . $videoInfo. json_encode($parts));
        }
        // Convert strings to integers
        $width = (int)$width;
        $height = (int)$height;

        $ffmpeg = FFMpeg::create([
            'ffmpeg.binaries' => env('FFMPEG'),
            'ffprobe.binaries' => env('FFPROBE')
        ]);
        $video = $ffmpeg->open($inputPath);
        $landscapeWidth = $video->getStreams()->first()->get('width');
        $landscapeHeight = $video->getStreams()->first()->get('height');


        if($this->fast) {
            $this->dashGenerate($storagePath,$ffmpegPath,$inputPath,$media,$media_path);
            return 0;
        }


        // Check if video needs conversion
        $needsConversion = $codec !== 'h264' || $height < 1080;
        if ($landscapeWidth > $landscapeHeight || $landscapeWidth == $landscapeHeight || ($landscapeHeight / $landscapeWidth) < 1.7) {
            $needsConversion = true;
        }

        if ($needsConversion) {
            $convertedPath = "$storagePath/converted_" . basename($inputPath);
            $convertedPath = str_replace('//', '/', $convertedPath);

            // Scale to fit within 1080x1920, then pad if necessary
            $scaleFilter = "scale=1080:1920:force_original_aspect_ratio=decrease,pad=1080:1920:(ow-iw)/2:(oh-ih)/2:black";

            $cmd = implode(' ', [
                "$ffmpegPath -y -i \"$inputPath\"",
                "-vf \"$scaleFilter,format=yuv420p\"",
                "-c:v libx264 -preset slower -crf 23",
                "-x264-params ref=4:me=umh:subme=7",
                "-movflags +faststart",
                "-c:a aac -b:a 128k",
                "-profile:v high -pix_fmt yuv420p",
                "\"$convertedPath\""
            ]);

            shell_exec($cmd);
            $inputPath = $convertedPath;

            // Re-analyze video after conversion
            $videoInfo = trim(shell_exec($ffprobeCmd));
            try {
                // Обработка разных форматов вывода
                $parts = explode("x", $videoInfo);

                if (count($parts) === 3) {
                    // Формат: widthxheightxcodec
                    [$width, $height, $codec] = $parts;
                } elseif (count($parts) === 4) {
                    // Формат: codecxwidthxheightx (как в случае с HEVC)
                    $codec = $parts[0];
                    $width = $parts[1];
                    $height = $parts[2];
                } else {
                    [$width, $height, $codec] = explode("x", $videoInfo);
                }
                // Convert strings to integers
                $width = (int)$width;
                $height = (int)$height;
            } catch( \Throwable $e ) {
                [$width, $height, $codec] = explode("x", $videoInfo);
            }

        }

        $fileName = 'thumb_' . $story->id . '.jpg';
        $video_thumbnail = new VideoThumbnail();
        $video_thumbnail->createThumbnail(
            $inputPath,
            $storagePath,
            $fileName,
            0,
            $width = 607,
            $height = 1080
        );
        $media->thumbnail = str_replace('//', '/', $media_path) . $fileName;
        $media->saveQuietly();

        // Convert strings to integers
        $width = (int)$width;
        $height = (int)$height;

        $isVertical = $height > $width;
        $tempVerticalPath = null;

        if ($story->challenge_id) {
            $watermarkedPath = "$storagePath/watermarked_source.mp4";

            $cmd = implode(' ', [
                "$ffmpegPath -y -i \"$inputPath\"",
                "-i \"$watermarkPath\"",
                "-filter_complex \"[1:v]scale=iw*2:ih*2[wm];[0:v][wm]overlay=W-w-100:170:enable='between(t,0,30)'\"",
                "-codec:a copy",
                "\"$watermarkedPath\""
            ]);

            shell_exec($cmd);
            $inputPath = $watermarkedPath;
        }

        $tempMp4Path = null;
        if (pathinfo($inputPath, PATHINFO_EXTENSION) === 'mov' || $codec === 'hevc') {
            $tempMp4Path = "$storagePath/temp_mp4_converted.mp4";

            $cmd = implode(' ', [
                "$ffmpegPath -y -i \"$inputPath\"",
                "-c:v libx264 -preset fast -crf 18",
                "-c:a aac -strict experimental",
                "\"$tempMp4Path\""
            ]);

            shell_exec($cmd);
            $inputPath = $tempMp4Path;
        }

        // If video is horizontal, convert to vertical
        if (!$isVertical) {
            $tempVerticalPath = "$storagePath/temp_vertical.mp4";

            $targetHeight = round($width * 16 / 9);

            $cmd = implode(' ', [
                "$ffmpegPath -y -i \"$inputPath\"",
                "-vf \"scale={$width}:-1,pad=iw:{$targetHeight}:(ow-iw)/2:(oh-ih)/2:black\"",
                "-c:a copy -c:v libx264 -preset fast -crf 18",
                "\"$tempVerticalPath\""
            ]);

            shell_exec($cmd);
            $inputPath = $tempVerticalPath;
        }

        $media->folder = $media_path;
        $converted_name = "converted_" . $media->name;
        $media->name = $converted_name;
        $media->slug = $converted_name;
        $media->slug_ext = $converted_name;
        $media->saveQuietly();

        $filter = 'scale=-2:ih';

        // Create HLS streams
        foreach ($this->resolutions as $label => $settings) {
            $outputFile = "$storagePath/hls/{$label}.m3u8";
            if (!file_exists(dirname($outputFile))) {
                mkdir(dirname($outputFile), 0755, true);
            }

            $bitrate = $settings['video_bitrate'];
            $audioBitrate = $settings['audio_bitrate'];
            $resHeight = $settings['height'];

            $cmd = implode(' ', [
                "$ffmpegPath -y -i \"$inputPath\"",
                "-vf \"$filter,scale=-2:$resHeight,format=yuv420p\"",
                "-c:a aac -ar 48000 -b:a {$audioBitrate}k",
                "-c:v h264 -profile:v main -crf 20 -sc_threshold 0",
                "-g 48 -keyint_min 48 -b:v {$bitrate}k -maxrate {$bitrate}k -bufsize " . ($bitrate * 2) . "k",
                "-hls_time 4 -hls_playlist_type vod",
                "-hls_segment_filename \"$storagePath/hls/{$label}_%03d.ts\"",
                "\"$outputFile\""
            ]);

            shell_exec($cmd);
        }

        $this->dashGenerate($storagePath,$ffmpegPath,$inputPath,$media,$media_path);

        // Generate master playlists

        // HLS master playlist
        $hlsMaster = "#EXTM3U\n";
        foreach ($this->resolutions as $label => $settings) {
            $bandwidth = $settings['video_bitrate'] * 1024;
            $hlsMaster .= "#EXT-X-STREAM-INF:BANDWIDTH={$bandwidth},RESOLUTION=1080x{$settings['height']}\n";
            $hlsMaster .= "hls/$label.m3u8\n";
        }
        file_put_contents("$storagePath/hls_master.m3u8", $hlsMaster);

        // Clean up temporary files
        if ($tempVerticalPath && file_exists($tempVerticalPath)) {
            unlink($tempVerticalPath);
        }
        if ($tempMp4Path && file_exists($tempMp4Path)) {
            unlink($tempMp4Path);
        }

        if (isset($watermarkedPath) && file_exists($watermarkedPath)) {
            unlink($watermarkedPath);
        }

        $story->media->update([
            'hls_url' => $media_path . 'hls_master.m3u8',
            'dash_url' => $media_path . 'dash/manifest.mpd'
        ]);

        $story->is_converted = true;
        $story->active = 1;
        $story->saveQuietly();

//        $this->sendTgMessage('ProcessVideo success ' . $story->id, 190036322);
        Log::info('ProcessVideo success ' . $story->id);
        return 0;


    }

    public function dashGenerate($storagePath,$ffmpegPath,$inputPath,$media,$media_path) {
        // Create DASH streams
        $dashDir = "$storagePath/dash";
        if (!file_exists($dashDir)) {
            mkdir($dashDir, 0755, true);
        }

        // Временные файлы
        $tempVideoFiles = [];
        $index = 0;

        // Шаг 1: Генерация видеофайлов разного качества
        foreach ($this->resolutions as $label => $settings) {
            $height = $settings['height'];
            $bitrate = $settings['video_bitrate'];
            $outFile = "$dashDir/video_{$height}p.mp4";

            $cmd = implode(' ', [
                "$ffmpegPath -y",
                "-i \"$inputPath\"",
                "-c:v libx264",
                "-b:v {$bitrate}k",
                "-vf scale=-2:$height,setsar=1,setdar=9/16",
                "-preset fast",
                "-crf 20",
                "-an", // без звука
                "\"$outFile\""
            ]);

            shell_exec($cmd);
            $tempVideoFiles[] = $outFile;

            // Дополнительно создаем версию 1080p с аудио
            if ($height == 1080) {
                $outFileWithAudio = "$dashDir/video_1080p_audio.mp4";
                $cmdWithAudio = implode(' ', [
                    "$ffmpegPath -y",
                    "-i \"$inputPath\"",
                    "-c:v libx264",
                    "-b:v {$bitrate}k",
                    "-vf scale=-2:$height,setsar=1,setdar=9/16",
                    "-preset fast",
                    "-crf 20",
                    "-c:a aac",
                    "-b:a 128k",
                    "\"$outFileWithAudio\""
                ]);
                shell_exec($cmdWithAudio);
            }
        }

        // Шаг 2: Генерация аудиофайла
        $audioFile = "$dashDir/audio.mp4";
        $cmdAudio = implode(' ', [
            "$ffmpegPath -y",
            "-i \"$inputPath\"",
            "-vn",
            "-c:a aac",
            "-b:a 128k",
            "\"$audioFile\""
        ]);
        shell_exec($cmdAudio);

        // Шаг 3: Объединение в MPEG-DASH
        $dashManifest = "$dashDir/manifest.mpd";

        // Команда ffmpeg: собираем input'ы
        $ffmpegInputs = '';
        foreach ($tempVideoFiles as $file) {
            $ffmpegInputs .= "-i \"$file\" ";
        }
        $ffmpegInputs .= "-i \"$audioFile\" ";

        // Формируем -map для видео и аудио
        $mapArgs = '';
        for ($i = 0; $i < count($tempVideoFiles); $i++) {
            $mapArgs .= "-map {$i}:v ";
        }
        $mapArgs .= "-map " . count($tempVideoFiles) . ":a"; // аудио — последним

        // Собираем финальную команду
        $dashCmd = implode(' ', [
            "$ffmpegPath -y",
            $ffmpegInputs,
            $mapArgs,
            "-c copy",
            "-f dash",
            "-seg_duration 4",
            "-use_template 1",
            "-use_timeline 1",
            "-adaptation_sets \"id=0,streams=v id=1,streams=a\"",
            "-init_seg_name init-\\\$RepresentationID\\\$.mp4",
            "-media_seg_name chunk-\\\$RepresentationID\\\$-\\\$Number\\\$.mp4",
            "\"$dashManifest\""
        ]);

        shell_exec($dashCmd);


        // Verify DASH output was created
        if (!file_exists("$dashDir/manifest.mpd")) {

        } else {

            // Удаляем все видеофайлы video_*.mp4 в папке $dashDir (или другой, где они лежат)
            $files = glob($dashDir . '/video_*.mp4');

            foreach ($files as $file) {
                if(str_contains($file, 'video_1080p') || str_contains($file, '1080p_audio')) {
                    continue;
                }
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }

        $media->dash_url = $media_path . 'dash/manifest.mpd';
        $media->saveQuietly();

        GetErid::dispatch($this->story_id);
    }
}
