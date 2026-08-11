<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Jobs\GetStoryTags;
use App\Models\Payment;
use App\Models\Story;
use App\Models\Thanks;
use Illuminate\Console\Command;
use Intervention\Image\Facades\Image;

class AddTagsToStories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stories:tags:add';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add tags to current stories';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $stories = Story::orderBy('created_at', 'DESC')->where('active', true)->whereHas('media', function($q): void {
            $q->where('mime_type', 'like', '%video%');
        })->doesntHave('tags')->get();

        foreach ($stories as $story){
            GetStoryTags::dispatch($story);
        }
    }
}
