<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Jobs\GetStoryTags;
use App\Models\Payment;
use App\Models\Story;
use App\Models\Thanks;
use App\Models\Transaction;
use Illuminate\Console\Command;
use Intervention\Image\Facades\Image;

class ReSaveTransactionsMeta extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'transactions:metas:resave';

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
        $transactions = Transaction::whereNotNull('meta')->get();
        foreach ($transactions as $transaction) {
            $meta = $transaction->meta;
            if(isset($meta['description'])) {
                $meta['description'] = $meta['description'].' ';
            }
//            $transaction->update(['meta' => $meta]);
        }
    }
}
