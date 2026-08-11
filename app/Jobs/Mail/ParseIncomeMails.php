<?php

namespace App\Jobs\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class ParseIncomeMails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $mailbox;
    private $page;

    public function __construct($mailbox, $page)
    {
        $this->mailbox = $mailbox;
        $this->page = $page;
        $this->queue = 'parsing';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $client = \Webklex\IMAP\Facades\Client::account($this->mailbox);
            $client->connect();
            $folder = $client->getFolder('INBOX');
            $per_page = 50;
            $messages = $folder->messages()->all()->paginate($per_page, $this->page);

            $emails = [];

            foreach($messages as $message){
                $message_body = $message->getTextBody();
                $subject = $message->getSubject();
                $filter = false;
                $unsubscribe = false;
                $spam = false;
                if(Str::contains($subject, 'Mail delivery failed')) {
                    $filter = true;
                }
                if(Str::contains($subject, 'Undelivered Mail Returned to Sender')) {
                    $filter = true;
                }
                if(Str::contains($subject, 'Undeliverable')) {
                    $filter = true;
                }
                if(Str::contains($subject, 'mailbox is disabled')) {
                    $filter = true;
                }
                if(Str::contains($subject, 'out of storage')) {
                    $filter = true;
                }
                if($filter) {
                    $rejections = [
                        'This domain is not in use',
                        'all relevant MX records point to non-existent hosts',
                        'No such user',
                        'mailbox full',
                        'mailbox is full',
                        'mailbox full',
                        'recipient address rejected',
                        'The email account that you tried to reach does not exist',
                        'reach does not exist',
                        'Unrouteable address',
                        'is out of storage space and inactive',
                        'recipient address rejected',
                        'after RCPT TO',
                        'retry timeout exceeded',
                        'Unrouteable address',
                        'Host or domain name not found',
                        'invalid mailbox',
                        'does not accept mail',
                        'mailbox is disabled',
                        'no mailbox here',
                        'No such user',
                        'account is disabled',
                        'unavailable or access denied',
                        'address rejected',
                        '550 spam message',
                    ];

                    foreach ($rejections as $rejection) {
                        if(Str::contains($message_body, $rejection)) {
                            $unsubscribe = true;
                        }
                    }


                    if(Str::contains($message_body, '550 spam message rejected')) {
                        $spam = true;
                    }
                    if(Str::contains($message_body, 'best protect our users from spam')) {
                        $spam = true;
                    }
                    if(Str::contains($message_body, 'message has been blocked')) {
                        $spam = true;
                    }

                    if(Str::contains($message_body, 'Message rejected under suspicion of SPAM')) {
                        $spam = true;
                    }

                    $pattern = '/[a-z0-9_\-\+\.]+@[a-z0-9\-]+\.([a-z]{2,4})(?:\.[a-z]{2})?/i';
                    preg_match_all($pattern, $message_body, $matches);
                    if(isset($matches[0][0])) {
                        $email = $matches[0][0];
                        if($unsubscribe) {
                            $emails[] = $email;
                            try {
                                $message->setFlag('Seen');
                                $message->move('WORKED');
                            } catch (\Throwable $e) {

                            }
                        } elseif($spam) {
                            try {
                                $message->setFlag('Seen');
                                $message->move('SPAMREPORT');
                            } catch (\Throwable $e) {

                            }
                        } else {
                            try {
                                $message->move('INCOME');
                            } catch (\Throwable $e) {

                            }
                        }
                    }
                } else {
                    try {
                        $message->move('INCOME');
                    } catch (\Throwable $e) {

                    }
                }

            }

            if(!empty($emails)) {
                $emails = array_unique($emails);
                try {
                    \DB::table('users')
                        ->whereIn('email',$emails)
                        ->whereNull('unsubscribe')
                        ->update([
                            'unsubscribe' => true,
                        ]);
                } catch (\Throwable $e) {

                }
            }
        } catch (\Throwable $e) {
//            Log::info($e->getMessage());
        }

    }

    public function failed(\Throwable $e)
    {

    }
}
