<?php

declare(strict_types=1);

namespace App\Console\Commands\Challenges;

use App\Models\Challenge;
use App\Models\Story;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UnfrozeChallenges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenges:unfroze';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get challenges need to be unfrozen';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $now = Carbon::now();
        $challenges = Challenge::where('finished', false)
            ->where('frozen', true)
            ->whereNull('finished_at')->get();
        foreach ($challenges as $challenge) {
            if($challenge->frozen_at) {
                $diff = $now->diffInHours(Carbon::parse($challenge->frozen_at));
                if($diff >= 48) {
                    $challenge->frozen = false;
                    $challenge->frozen_at = null;
                    $challenge->save();
                    $stories = Story::where('challenge_id', $challenge->id)->where('frozen', true)->get();
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
