<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class Newsletter extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public $email;
    public $mailing;
    public $body;
    public $usermail;
    public $hostname;
    public function __construct($email, $mailing, $body, $usermail, $hostname = null)
    {
        $this->email = $email;
        $this->mailing = $mailing;
        $this->body = $body;
        $this->usermail = $usermail;
        $this->hostname = $hostname ?? 'deels.ru';
    }

    /**
     * Get the message envelope.
     *
     * @return \Illuminate\Mail\Mailables\Envelope
     */
    public function envelope()
    {
        return new Envelope(
            subject: $this->mailing->subject,
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array
     */
    public function attachments()
    {
        return [];
    }

    public function build()
    {
        $unsubscribeUrl = "https://{$this->hostname}/unsubscribe/" . encrypt($this->email);
        $unsubscribeEmail = "unsubscribe@{$this->hostname}";

        $email = $this->from(env('MAIL_FROM_ADDRESS_NEWSLETTER', env('MAIL_FROM_ADDRESS')), 'DEELS')
            ->subject($this->mailing->subject)
            ->view('newsletters.mail')
            ->text('newsletters.mail_text')
            ->with([
                'body' => $this->body,
                'usermail' => $this->usermail,
                'mailing' => $this->mailing ?? null,
                'unsubscribeUrl' => $unsubscribeUrl,
            ]);

        // Move header logic here directly if you’re queueing mail
        $email->withSymfonyMessage(function ($message) use ($unsubscribeUrl, $unsubscribeEmail) {
            $headers = $message->getHeaders();
            $headers->addTextHeader('List-Unsubscribe', "<{$unsubscribeUrl}>, <mailto:{$unsubscribeEmail}>");
            $headers->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        });

        return $email;
    }
}
