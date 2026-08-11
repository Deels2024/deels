<?php

namespace App\Jobs;

use App\Models\Story;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckTGStoryModeration implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $story_id;
    private $moderator_id;

    public function __construct($story_id, $moderator_id)
    {
        $this->story_id = $story_id;
        $this->moderator_id = $moderator_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $story = Story::find($this->story_id);
        if($story) {
            if(!$story->moderated) {
                SendTGStoryModeration::dispatch($story, $this->moderator_id);
            }
        }
    }
}
