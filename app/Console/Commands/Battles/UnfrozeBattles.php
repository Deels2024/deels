<?php

declare(strict_types=1);

namespace App\Console\Commands\Battles;

use App\Models\Battle;
use App\Models\Challenge;
use App\Models\Story;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UnfrozeBattles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'battles:unfroze';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get battles need to be unfrozen';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $now = Carbon::now();
        $battles = Battle::where('finished', false)
            ->where('frozen', true)
            ->whereNull('finished_at')->get();
        foreach ($battles as $battle) {
            if($battle->frozen_at) {
                $diff = $now->diffInHours(Carbon::parse($battle->frozen_at));
                if($diff >= 48) {
                    $battle->frozen = false;
                    $battle->frozen_at = null;
                    $battle->save();
                    $stories = Story::where('battle_id', $battle->id)->where('frozen', true)->get();
                    foreach ($stories as $story) {
                        $story->frozen = false;
                        $story->frozen_at = null;
                        $story->saveQuietly();
                    }
                }
            }
        }
    }
}
