<?php

declare(strict_types=1);

namespace App\Console\Commands\Stories;


use App\Helpers\AppHelper;
use App\Jobs\Stories\ProcessVideo;
use App\Jobs\SystemNotify;
use App\Models\Story;
use Illuminate\Console\Command;

class MassProcessVideo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'video:mass:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process videos stories';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $stories = Story::where('active', 1)
            ->where('declined', 0)->whereHas('media', function ($q): void {
                $q->whereNull('hls_url');
            })->get();

        $tasks = 0;
        foreach ($stories as $story) {
            ProcessVideo::dispatch($story->id, true);
            $tasks++;
        }

        echo "ProcessVideo stories tasks: $tasks\n";

        if ($tasks > 0) {
            $telegram = new AppHelper();
            $telegram->telegram_message('ProcessVideo stories tasks: ' . $tasks);
        }

        SystemNotify::dispatch('✅ ProcessVideo ends');

    }
}
