<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Payment;
use App\Models\PendingReferrals;
use App\Models\Thanks;
use App\Models\User;
use Illuminate\Console\Command;

class SuccessPaymentsThanksModerated extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:thanks-moderate';

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
        $thanks = Thanks::where('approved', true)->where('moderated', true)->get();
        foreach ($thanks as $thank) {
            if($thank->payment && $thank->payment->status == 'pending') {
                $thank->payment->update(['status' => 'success']);
            }
        }
    }
}
