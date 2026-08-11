<?php

namespace App\Jobs\Mail;

use App\Models\Mailing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class BatchSendNewsletterMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $newsletter_mails;

    public function __construct($newsletter_mails)
    {
        $this->newsletter_mails = $newsletter_mails;
        $this->queue = 'parsing';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->newsletter_mails as $mail) {
            \App\Jobs\Mail\SendNewsletterMail::dispatch($mail);
        }

    }

    public function failed(\Throwable $e)
    {

    }
}
