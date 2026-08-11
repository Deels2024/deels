<?php

namespace App\Notifications;


use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use NextApps\VerificationCode\Notifications\VerificationCodeCreatedInterface;

class AccountDeleteNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public string $content;

    public function __construct(string $content)
    {
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
            ->subject(__('Запрос на удаление аккаунта'))
            ->line(__(':body', ['body' => $this->content]));
    }
}