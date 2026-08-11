<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InactiveAccountDeletedMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function build(): self
    {
        return $this
            ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
            ->subject('Ваш аккаунт на Deels удалён')
            ->view('emails.inactive_account_deleted')
            ->text('emails.inactive_account_deleted_text');
    }
}
