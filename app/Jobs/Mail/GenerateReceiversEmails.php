<?php

namespace App\Jobs\Mail;

use App\Models\NewsletterMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class GenerateReceiversEmails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $users;
    private $mailing;
    private $hours;

    public function __construct($users, $mailing, $hours = 0)
    {
        $this->users = $users;
        $this->mailing = $mailing;
        $this->hours = $hours;
        $this->queue = 'parsing';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        foreach ($this->users as $user) {
            $newsletter_mail = new NewsletterMail();
            $newsletter_mail->newsletter_id = $this->mailing->id;
            $newsletter_mail->email = $user->email;
            $newsletter_mail->token = Str::uuid()->toString();
            $newsletter_mail->save();
        }
    }

    public function failed(\Throwable $e)
    {
        $count = $this->newsletter->receivers()->count();
        if($count > 0) {
            $this->newsletter->update(['sent' => true, 'status' => 'processing', 'receivers_count' => $count]);
        }
    }
}
