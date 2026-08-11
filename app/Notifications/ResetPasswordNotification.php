<?php

declare(strict_types=1);

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    public $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $token = explode('?token=', $this->token);

        return (new MailMessage())
            ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
            ->subject('Восстановление пароля на DEELS')
            ->line('Вы получили это письмо, так как запросили восстановление пароля на сервисе DEELS')
            ->action('Восстановить пароль', route('password.reset', $token[1]))
            ->line('Если вы не совершали данного действия, игнорируйте письмо и напишите в службу поддержки.');
    }
}
