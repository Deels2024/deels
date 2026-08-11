<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Jobs\GetStoryTags;
use App\Jobs\Mail\SendNewsletterDemoMail;
use App\Models\Payment;
use App\Models\Story;
use App\Models\Thanks;
use Illuminate\Console\Command;
use Intervention\Image\Facades\Image;

class SendDemoMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mails:send:demo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        for ($i = 1; $i <= 50000; $i++) {
            SendNewsletterDemoMail::dispatch($i);
        }
    }
}
