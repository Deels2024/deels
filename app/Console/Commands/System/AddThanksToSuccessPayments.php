<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Models\Payment;
use App\Models\Thanks;
use Illuminate\Console\Command;
use Intervention\Image\Facades\Image;

class AddThanksToSuccessPayments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'campaigns:payments:thanks';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add thanks to success payments';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $payments = Payment::whereIn('status', ['success','pending'])->doesntHave('thanks')->get();
        echo "found ".count($payments)." payments \n";
        foreach ($payments as $payment) {
            $data = [
                'payment_id' => $payment->id,
                'data' => ['type' => 'comment', 'payload' => '']
            ];
            if($payment->status == 'pending') {
                $payment->status == 'success';
                $payment->save();
            }
            Thanks::create($data);
        }
    }
}
