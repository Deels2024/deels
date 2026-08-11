<?php

namespace App\Jobs\Mail;

use App\Mail\Newsletter;
use App\Models\Mailing;
use App\Models\News;
use App\Models\NewsletterMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendNewsletterDemoMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $mail;
    private $number;

    public function __construct($i = null)
    {
        $this->number = $i ?? null;
        $this->queue = 'newsletters';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Log::info('SendNewsletterDemoMail '.$this->number.'success');
        try {
            config([
                'mail' => [
                    'driver' => 'smtp',
                    'host' => 'smtp.ethereal.email',
                    'port' => 587,
                    'encryption' => null,
                    'username' => 'isai.gerlach@ethereal.email',
                    'password' => 'jyPv6ghBytrz7b4rFj',
                ],
            ]);
            $mailing = Mailing::find(1);
            $body = 'test';
            $mail = Mail::to('test@deels.ru')->send(new Newsletter('test@deels.ru', $mailing, $body));
        } catch (\Throwable $e) {

        }

    }

    public function failed(\Throwable $e)
    {

    }
}
