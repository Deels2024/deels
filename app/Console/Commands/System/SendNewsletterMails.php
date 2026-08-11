<?php

namespace App\Console\Commands\System;

use App\Jobs\Mail\BatchSendNewsletterMail;
use App\Jobs\SendNewsletterMail;
use App\Models\NewsletterMail;
use Illuminate\Console\Command;

class SendNewsletterMails extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'newsletters:mails:send';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send pending mails';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $count = NewsletterMail::where('status', 'pending')->count();
        NewsletterMail::where('status', 'pending')->chunk(1000, function($newsletter_mails) {
            foreach ($newsletter_mails as $mail) {
                $mail->status = 'sending';
                $mail->saveQuietly();
            }
            BatchSendNewsletterMail::dispatch($newsletter_mails);
        });
        echo "Processing $count mails \n";
    }
}
