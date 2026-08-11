<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class VerificationCodeMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $code;

    public function __construct(string $code)
    {
        $this->code = $code;
    }

    public function build(): self
    {
        return $this
            ->from(env('MAIL_FROM_ADDRESS', 'info@deels.ru'), 'DEELS')
            ->subject('Подтверждение почты DEELS')
            ->view('emails.verification_code')
            ->text('emails.verification_code_text');
    }
}
