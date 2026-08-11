<?php

declare(strict_types=1);

namespace App\Mail;

use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactUs extends Mailable
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
        return $this->from(get_option('email_address'))->to(get_option('email_address'))->subject('['.get_option('site_name').'] Contact Us Query')->markdown('emails.contact_us')->with([
            'name' => $this->request->name,
            'email' => $this->request->email,
            'subject' => $this->request->subject,
            'message' => $this->request->message."<br><br> Отправлено: ".Carbon::now()->format('d.m.Y в H:i:s'),
            'project_owner' => $this->request->project_owner,
            'project_backer' => $this->request->project_backer,
            'other' => $this->request->other,
        ]);
    }
}
