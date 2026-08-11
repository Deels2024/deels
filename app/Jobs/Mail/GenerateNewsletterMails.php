<?php

namespace App\Jobs\Mail;

use App\Helpers\AppHelper;
use App\Models\NewsletterMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GenerateNewsletterMails implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    private $newsletter;

    public function __construct($newsletter)
    {
        $this->newsletter = $newsletter;
        $this->queue = 'parsing';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mailing = $this->newsletter;
        $this->newsletter->receivers()->delete();
        $users = $mailing->users;
        $users_count = 0;
        $telegram = new AppHelper();
        $telegram->telegram_message('Start newsletter ID' . $this->newsletter->id);
        $hours = 0;
        $chunks = 0;
        if (!$users) {
            $users_count_query = DB::table('users')
                ->select('email')
                ->where('is_activated', true)
                ->whereNull('unsubscribe');
            $users_query = DB::table('users')
                ->select('email')
                ->whereNull('unsubscribe')
                ->where('is_activated', true)
                ->orderBy('id');
            if ($mailing->gmail_exclude) {
                $users_count_query->where('email', 'not like', '%gmail%');
                $users_query->where('email', 'not like', '%gmail%');
            }


            $users_count = $users_count_query->count();

            $users = $users_query->chunk(1000, function ($users) use ($mailing, &$hours, &$chunks) {
                $chunks = $chunks + 1000;
                if ($chunks > 0 && $chunks % 4000 === 0) {
                    $hours++;
                }
//                Log::info("chunk:".$chunks." \  add hours from now: ".$hours. ' \ sending time:'.Carbon::now()->addHours($hours)->format('d.m.Y H:i:s'));
//                GenerateReceiversEmails::dispatch($users, $mailing, $hours)->delay(Carbon::now()->addHours($hours));
                GenerateReceiversEmails::dispatch($users, $mailing, $hours);
            });

        } elseif ($users[0] === 'all') {
            $users_count_query = User::query()
                ->whereNull('unsubscribe')
                ->wherehas('my_campaigns');

            $users_query = User::query()
                ->whereNull('unsubscribe')
                ->wherehas('my_campaigns')
                ->orderBy('id');
            if ($mailing->gmail_exclude) {
                $users_count_query->where('email', 'not like', '%gmail%');
                $users_query->where('email', 'not like', '%gmail%');
            }

            $users_count = $users_count_query->count();
            $users = $users_query->chunk(1000, function ($users) use ($mailing, &$hours, &$chunks) {
                $chunks = $chunks + 1000;
                if ($chunks > 0 && $chunks % 4000 === 0) {
                    $hours++;
                }
//                Log::info("chunks:".$chunks." hours: ".$hours);
                GenerateReceiversEmails::dispatch($users, $mailing);
            });


        } else {
            foreach ($users as $user) {
                if($mailing->gmail_exclude) {
                    if(Str::contains($user, 'gmail')) {
                        continue;
                    }
                }
                $newsletter_mail = new NewsletterMail();
                $newsletter_mail->newsletter_id = $mailing->id;
                $newsletter_mail->email = $user;
                $newsletter_mail->token = Str::uuid()->toString();
                $newsletter_mail->save();
            }
            $users_count = count($users);
        }
        $mailing->update(['sent' => true, 'status' => 'processing', 'receivers_count' => $users_count]);
    }

    public function failed(\Throwable $e)
    {
        $count = $this->newsletter->receivers()->count();
        $this->newsletter->update(['sent' => true, 'status' => 'fail']);
        $telegram = new AppHelper();
        $telegram->telegram_message('Newsletter ID' . $this->newsletter->id . ' fails! ' . $e->getMessage());
        if ($count > 0) {
            $this->newsletter->update(['sent' => true, 'status' => 'processing', 'receivers_count' => $count]);
        }
    }
}
