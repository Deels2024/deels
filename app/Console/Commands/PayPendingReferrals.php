<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Campaign;
use App\Models\Payment;
use App\Models\PendingReferrals;
use App\Models\User;
use Illuminate\Console\Command;

class PayPendingReferrals extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:pay-referrals';

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
        PendingReferrals::query()
                        ->where('paid', false)
                        ->each(function(PendingReferrals $pf): void {
                            dump($pf);
                            $invitor      = User::find($pf->user_id);
                            dump(User::where('invite_referral_code', $invitor->referral_code)->count());
                            if (User::where('invite_referral_code', $invitor->referral_code)->count() >= 5) {
//                                dd(12312);
                                Campaign::query()
                                        ->where('user_id', $pf->user_id)
                                        ->orderBy('id')
                                        ->get()
                                        ->each(function(Campaign $campaign) use ($pf): void {
                                            if ($campaign->percent_raised() < 100) {
                                                $data                = $pf->data;
                                                $data['campaign_id'] = $campaign->id;
                                                $payment             = Payment::create($data);

                                                $pf->update([
                                                    'paid'       => true,
                                                    'payment_id' => $payment->id,
                                                ]);
                                            }
                                        });
                            }
                        });
    }
}
