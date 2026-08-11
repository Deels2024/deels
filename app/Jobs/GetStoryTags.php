<?php

namespace App\Jobs;

use App\Helpers\ChatGPTHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GetStoryTags implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $story;

    public function __construct($story)
    {
        $this->story = $story;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $chatgpt = new ChatGPTHelper();

        try {
            $response = $chatgpt->stories_data_by_video($this->story->media);
            if(isset($response['tags'])) {
                $new_tags = [];
                foreach ($response['tags'] as $tag) {
                    $new_tag = \App\Models\Tag::firstOrCreate([
                        'title' => $tag,
                    ]);

                    $new_tags[] = $new_tag->id;
                }
                $this->story->tags()->sync($new_tags);
                $this->story->meta_title = $response['title'];
                $this->story->meta_description = $response['description'];
                $this->story->saveQuietly();
                if(empty($new_tags)) {
                    Log::info('No tags for story ID'.$this->story->id);
                }
            } else {
                Log::info(['GetStoryTags tags issue ID'.$this->story->id,$response]);
//                self::dispatch($this->story);
            }
        } catch (\Throwable $e) {
            Log::info('GetStoryTags: '.$e->getMessage());
            if(!Str::contains($e->getMessage(), 'Failed to open stream: No such file or directory')) {
//                self::dispatch($this->story)->delay(40);
            }
        }

    }
}
