<?php

namespace App\Notifications;


use App\Jobs\SendTGPMNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NextApps\VerificationCode\Notifications\VerificationCodeCreatedInterface;

class UserEmail extends Notification implements ShouldQueue
{
    use Queueable;

    public string $content;
    public string $subject;

    public function __construct(string $subject, string $content)
    {
        $this->subject = $subject;
        $this->content = $content;
    }

    public function via() : array
    {
        return ['mail'];
    }

    public function toMail($notifiable) : MailMessage
    {
        return (new MailMessage())
            ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
            ->subject(__($this->subject))
            ->line(__(':body', ['body' => $this->content]));
    }
}