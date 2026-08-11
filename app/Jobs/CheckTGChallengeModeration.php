<?php

namespace App\Jobs;

use App\Models\Challenge;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckTGChallengeModeration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $challenge_id;
    private $moderator_id;

    public function __construct($challenge_id, $moderator_id)
    {
        $this->challenge_id = $challenge_id;
        $this->moderator_id = $moderator_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $challenge = Challenge::find($this->challenge_id);
        if($challenge) {
            if(!$challenge->moderated) {
                SendTGChallengeModeration::dispatch($challenge, $this->moderator_id);
            }
        }
    }
}
