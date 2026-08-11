<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Helpers\AppHelper;
use App\Models\Payment;
use App\Services\TinkoffService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;

class GetRebillPaymentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'services:rebill';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $from = Carbon::now()->format('Y-m-d 00:00:00');
        $to = Carbon::now()->format('Y-m-d 23:59:59');
        $payments = Payment::whereNotNull('rebill_id')
            ->whereNotNull('rebill_at')
            ->where('rebill_at', '>=', $from)
            ->where('rebill_at', '<=', $to)
            ->get();

        foreach ($payments as $payment) {
            try {
                $donation_amount = $payment->amount*100;
                $payment->user->wallet_withdraw(intval($donation_amount), ['donate' => 'campaign']);
                $new_payment = Payment::create([
                    'name' => $payment->user->name,
                    'email' => $payment->user->email,
                    'campaign_id' => $payment->campaign_id,
                    'user_id' => Auth::id(),
                    'amount' => ($donation_amount/100),
                    'payment_method' => 'coins',
                    'status' => 'success',
                    'rebill_id' => null,
                    'rebill_at' => null,
                ]);
                $payment->rebill_at = Carbon::now()->addMonth();
                $payment->save();
            } catch (\Throwable $e) {
                echo $e->getMessage()."\n";
            }
        }
    }
}
