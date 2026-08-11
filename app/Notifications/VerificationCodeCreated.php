<?php

namespace App\Notifications;


use App\Mail\VerificationCodeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NextApps\VerificationCode\Notifications\VerificationCodeCreatedInterface;
use Telegram\Bot\Api;
use Telegram\Bot\Exceptions\TelegramResponseException;

class VerificationCodeCreated extends Notification implements ShouldQueue, VerificationCodeCreatedInterface
{
    use Queueable;

    public $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function via() : array
    {
        return ['mail'];
    }

    public function toMail($notifiable) : VerificationCodeMail
    {
        return (new VerificationCodeMail($this->code))
            ->to($notifiable->routeNotificationFor('mail'));
    }
}
