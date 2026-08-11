<?php

declare(strict_types=1);

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUsSendToSender extends Mailable
{
    use Queueable;
    use SerializesModels;

    protected $request;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->from(get_option('email_address'))->to($this->request->email)->subject('['.get_option('site_name').'] Contact Us Query Received')->markdown('emails.contact_us_send_to_sender')->with($this->request->input());
    }
}
