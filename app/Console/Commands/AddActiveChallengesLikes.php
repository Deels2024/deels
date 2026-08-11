<?php

namespace App\Console\Commands;

use App\Models\Challenge;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class AddActiveChallengesLikes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'challenges:add:likes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
            $challenges = Challenge::where('active', true)->where('finished', 0)->get();
            $likes = 1;

            $success = 0;
            foreach ($challenges as $challenge) {
                for ($i = 1; $i <= $likes; $i++) {
                    foreach ($challenge->stories as $story) {
                        if(in_array($story->user_id, [69139])) {
                            continue;
                        }
                        $story_id = $story->id;
                        $likes_count_loop = rand(1,3);
                        for ($likes_count = 1; $likes_count <= $likes_count_loop; $likes_count++) {
                            DB::table('likes')->insert(['story_id' => $story_id, 'campaign_id' => 0, 'user_id' => 12, 'created_at' => now()]);
                            $success++;
                        }
                        for ($comment_i = 1; $comment_i <= rand($likes_count_loop,5); $comment_i++) {
                            DB::table('views')->insert(['story_id' => $story_id,  'campaign_id' => 0, 'user_id' => 12, 'created_at' => now()]);
                        }
                    }

                }
            }
            echo "success $success\n";
    }
}
