<?php

declare(strict_types=1);

namespace App\Console\Commands\Challenges;

use App\Helpers\AppHelper;
use App\Helpers\ChatGPTHelper;
use App\Jobs\FinishChallenge;
use App\Jobs\SendMotivateStoryMessage;
use App\Models\Challenge;
use App\Models\Story;
use App\Notifications\StoryNotification;
use App\Notifications\UserEmail;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class StartActiveChallenges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenges:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get challenges need to be start';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $now = Carbon::now();
        $challenges = Challenge::whereNotNull('min_participants')
            ->where('active', true)
            ->where('started', false)
            ->where('finished', false)
            ->where('frozen', false)
            ->where('declined', false)->get();

        $start_count = 0;
        $finished_count = 0;
        $telegram = new AppHelper();
        if(count($challenges)) {
//            $telegram->telegram_message('Challenges start check: '.count($challenges));
        }

        foreach ($challenges as $challenge) {
            $scheduledStart = $challenge->date_from ?: $challenge->start;
            $canStartByTime = !$scheduledStart || Carbon::parse($scheduledStart)->lte($now);
            if($canStartByTime && $challenge->participants >= $challenge->min_participants) {
                $challenge->started = true;
                $challenge->start = Carbon::now();
                $start_count++;
            }
            $scheduledFinish = $challenge->date_to ?: $challenge->finish;
            if ($scheduledFinish && Carbon::parse($scheduledFinish)->isPast()) {
                FinishChallenge::dispatch($challenge);
                $finished_count++;
            }

            $challenge->save();
        }

        if(count($challenges)) {
            if($start_count > 0 || $finished_count > 0) {
                $telegram->telegram_message('Started challenges: '.$start_count.' \ Finished challenges: '.$finished_count);
            }

        }
    }
}
