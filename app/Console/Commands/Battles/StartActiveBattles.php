<?php

declare(strict_types=1);

namespace App\Console\Commands\Battles;

use App\Helpers\AppHelper;
use App\Helpers\ChatGPTHelper;
use App\Jobs\FinishBattle;
use App\Jobs\FinishChallenge;
use App\Jobs\SendMotivateStoryMessage;
use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Story;
use App\Notifications\StoryNotification;
use App\Notifications\UserEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StartActiveBattles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'battles:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get battles need to be start';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $now = Carbon::now();
        $battles = Battle::whereNotNull('min_participants')
            ->where('active', true)
            ->where('started', false)
            ->where('finished', false)
            ->where('frozen', false)
            ->where('declined', false)->get();

        $start_count = 0;
        $finished_count = 0;
        $telegram = new AppHelper();
        if(count($battles)) {
//            $telegram->telegram_message('Challenges start check: '.count($battles));
        }

        foreach ($battles as $battle) {
            $scheduledStart = $battle->date_from ?: $battle->start;
            $canStartByTime = !$scheduledStart || Carbon::parse($scheduledStart)->lte($now);
            if($canStartByTime && $battle->participants >= $battle->min_participants) {
                $battle->started = true;
                $battle->start = Carbon::now();
                $start_count++;
            }
            $scheduledFinish = $battle->date_to ?: $battle->finish;
            if ($scheduledFinish && Carbon::parse($scheduledFinish)->isPast()) {
                FinishBattle::dispatch($battle);
                $finished_count++;
            }
            $battle->save();
        }

        if(count($battles)) {
            if($start_count > 0 || $finished_count > 0) {
                $telegram->telegram_message('Started battles: '.$start_count.' \ Finished battles: '.$finished_count);
            }

        }
    }
}
