<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\AppHelper;
use App\Jobs\Mail\GenerateNewsletterMails;
use App\Models\Mailing;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;

class SendMailings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:send-mailings';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        try {
            $now = Carbon::now();

            $count = 0;
            Mailing::query()
                ->where('sent', false)
                ->where(function (Builder $builder): void {
                    $builder
                        ->where('date', '<=', Carbon::now()->addHours(3))
                        ->orWhereNull('date');
                })
                ->each(function (Mailing $mailing) use ($now, $count): void {

                    if ($now >= $mailing->sent_at) {
                        echo "Sending $mailing->id \n\n";
                        GenerateNewsletterMails::dispatch($mailing);
                        $mailing->update(['sent' => true, 'status' => 'processing']);
                    }
                });

            if($count > 0) {
                echo "Sending $count newsletters \n\n";
            }
        } catch (\Throwable $exception) {

        }

        return true;
    }
}
