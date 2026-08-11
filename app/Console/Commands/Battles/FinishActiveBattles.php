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

class FinishActiveBattles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'battles:finish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get battles need to be finished';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $now = Carbon::now();
        $battles = Battle::where('finished', false)
            ->where(function ($query) use ($now): void {
                $query->where('date_to', '<=', $now)
                    ->orWhere(function ($legacyQuery) use ($now): void {
                        $legacyQuery->whereNull('date_to')
                            ->where('finish', '<=', $now);
                    });
            })
            ->where('declined', false)
            ->where('frozen', false)
            ->whereNull('finished_at')->get();
        $telegram = new AppHelper();
        if(count($battles)) {
            $telegram->telegram_message('Battles finishing: '.count($battles));
        }
        foreach ($battles as $battle) {
            FinishBattle::dispatchSync($battle);
        }
    }
}
