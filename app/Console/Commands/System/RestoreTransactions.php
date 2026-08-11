<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Jobs\GetStoryTags;
use App\Models\Payment;
use App\Models\Story;
use App\Models\Thanks;
use App\Models\User;
use Bavix\Wallet\Models\Transaction;
use Bavix\Wallet\Models\Wallet;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;

class RestoreTransactions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:restore';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

//        $old_data_wallets = DB::connection('backup')->table('wallets')->where('id', '>', '746')->get();
//        echo "Found wallets: " . count($old_data_wallets) . "\n";
//        $count_wallets = 0;
//        foreach ($old_data_wallets as $old_data_wallet) {
//            $exists = Wallet::where('uuid', $old_data_wallet->uuid)->first();
//            $payment = Wallet::updateOrCreate(
//                [
//                    'holder_type' => $old_data_wallet->holder_type,
//                    'holder_id' => $old_data_wallet->holder_id,
//                ],
//                [
//                    'holder_type' => $old_data_wallet->holder_type,
//                    'name' => $old_data_wallet->name,
//                    'slug' => $old_data_wallet->slug,
//                    'uuid' => $old_data_wallet->uuid,
//                    'description' => $old_data_wallet->description,
//                    'meta' => $old_data_wallet->meta,
//                    'balance' => $old_data_wallet->balance,
//                    'decimal_places' => $old_data_wallet->decimal_places,
//                    'created_at' => $old_data_wallet->created_at,
//                    'updated_at' => $old_data_wallet->updated_at,
//                ]
//            );
//            $count_wallets++;
//
//        }
//        echo "Restored wallets: " . $count_wallets . "\n";
        $old_data = DB::connection('backup')->table('transactions')->where('updated_at', '>=', '2023-03-10 00:00:00')->get();

        echo "Found transactions: " . count($old_data) . "\n";
        $count = 0;
        $count_not_exists = 0;
        foreach ($old_data as $old_item) {
            $exists = Transaction::where('uuid', $old_item->uuid)->first();
            if (!$exists) {
                $count_not_exists++;
                if($old_item->payable_type == 'App\Models\User') {
                    $user_wallet = Wallet::where('holder_type', 'App\Models\User')->where('holder_id', $old_item->payable_id)->first();
                    $transaction = new Transaction();
                    $transaction->payable_type = $old_item->payable_type;
                    $transaction->payable_id = $old_item->payable_id;
                    $transaction->wallet_id = $user_wallet->id;
                    $transaction->type = $old_item->type;
                    $transaction->amount = $old_item->amount;
                    $transaction->confirmed = $old_item->confirmed;
                    $transaction->meta = $old_item->meta ? json_decode($old_item->meta, true) : [];
                    $transaction->uuid = $old_item->uuid;
                    $transaction->created_at = $old_item->created_at;
                    $transaction->updated_at = $old_item->updated_at;
                    $transaction->save();
                    $count++;
                }

                try {

                } catch (\Throwable $e) {

                }

            } else {

            }

        }

        echo "Not exists: " . $count_not_exists . "\n";
        echo "Restored: " . $count . "\n";

    }
}
