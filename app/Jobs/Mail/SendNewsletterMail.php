<?php

namespace App\Jobs\Mail;

use App\Mail\Newsletter;
use App\Models\Mailing;
use App\Models\News;
use App\Models\NewsletterMail;
use http\Client\Curl\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Message;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SendNewsletterMail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $mail;

    public function __construct($mail)
    {
        $this->mail = $mail;
        $this->queue = 'newsletters';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $newsletter = $this->mail->newsletter;
        $mailing = NewsletterMail::find($this->mail->id);
        $email = $this->mail->email;

        preg_match('~src="(data:image[^"]*)"~', $mailing->text, $matches);
        $mailingText = $newsletter->text;
        if (isset($matches[1])) {
            [$type, $data] = explode(';', $matches[1]);
            [, $data] = explode(',', $data);
            file_put_contents(public_path('img_email.png'), base64_decode($data));
            $mailingText = str_replace([$matches[1], 'width: 558px;'], [
                'https://deels.ru/img_email.png?a=' . microtime(), 'width: 100%;',
            ], $mailingText);
        }

        $mailingText = preg_replace('/<a (.+ )?href="([^"]+)"([^>]+)?>/', '<a ${1} href="https://deels.ru/mail_track?action=click&mail_id=' . $newsletter->id . '&redirect=${2}"${3}>', $mailingText);
        $username = 'Друг';
        $user = \App\Models\User::where('email', $email)->first();
        if($user) {
            $username = $user->username;
        }
        $mailingText = STR::replace('[username]', $username, $mailingText);

        try {
            if($mailing->status == 'pending' || $mailing->status == 'sending') {
                $this->email($email, $mailingText, $newsletter, $mailing);
                $newsletter->increment('delivered');
                $this->mail->status = 'success';
                $this->mail->data = null;
                $this->mail->save();
            }
        } catch (\Throwable $e) {
            $this->mail->data = $e->getMessage();
            $this->mail->status = 'fail';
            $this->mail->save();
        }
        $this->mail->increment('sent_count');
        $newsletter->increment('sent_count');
//        if($newsletter->sent_count == $newsletter->receivers_count) {
//            $newsletter->status = 'done';
//            $newsletter->saveQuietly();
//        }

    }

    public function failed(\Throwable $e)
    {
        $this->mail->increment('sent_count');
        $this->mail->data = $e->getMessage();
        $this->mail->status = 'fail';
        $this->mail->save();
//        $this->mail->newsletter->increment('sent_count');
        if($this->mail->newsletter->sent_count == $this->mail->newsletter->receivers_count) {
            $this->mail->newsletter->status = 'done';
            $this->mail->newsletter->saveQuietly();
        }
    }

    private function email(string $email, string $body, Mailing $mailing, $usermail): void
    {
        config([
            'mail' => config('mail.mailers.newsletter')
        ]);
        config([
            'mail' => [
                'driver' => 'smtp',
                'host' => env('MAIL_HOST_NEWSLETTER', 'connect.smtp.bz'),
                'port' => env('MAIL_PORT_NEWSLETTER', 465),
                'encryption' => env('MAIL_ENCRYPTION_NEWSLETTER', 'ssl'),
                'username' => env('MAIL_USERNAME_NEWSLETTER','info@deels.ru'),
                'password' => env('MAIL_PASSWORD_NEWSLETTER', 'ssn9345253'),
                'verify_peer' => false,
                'from' => [
                    'address' => env('MAIL_USERNAME_NEWSLETTER','info@deels.ru'),
                    'name' => env('MAIL_USERNAME_NEWSLETTER','info@deels.ru'),
                ],
            ],
        ]);
        Mail::to($email)->send(new Newsletter($email, $mailing, $body, $usermail, env('MAIL_HOST_NAME_NEWSLETTER','deels.ru')));
    }
}
