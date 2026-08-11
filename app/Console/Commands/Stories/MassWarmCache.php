<?php

declare(strict_types=1);

namespace App\Console\Commands\Stories;

use App\Helpers\AppHelper;
use App\Helpers\ChatGPTHelper;
use App\Jobs\Cdnvideo\UploadMediaToCdnvideo;
use App\Jobs\Cdnvideo\WarmCache;
use App\Jobs\SendMotivateStoryMessage;
use App\Models\Story;
use App\Notifications\StoryNotification;
use App\Notifications\UserEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MassWarmCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stories:cdn:warm';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Warm all cdn stories';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $stories = Story::where('active', 1)
            ->where('declined', 0)->whereHas('media', function ($q): void {
                $q->whereNotNull('hls_url');
            })->get();

        $tasks = 0;
        foreach ($stories as $story) {
            WarmCache::dispatch($story->media)->delay(now()->addSeconds(7));
            $tasks++;
        }

        echo "Upload to cdn tasks: $tasks\n";

        if ($tasks > 0) {
            $telegram = new AppHelper();
            $telegram->telegram_message('Warm cdn stories tasks: ' . $tasks);
        }

    }
}
