<?php

namespace App\Jobs\User;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateUsersBalance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */

    private $user_id;
    private $coins_pack;
    private $meta_data;
    private $data;
    private $description;

    public function __construct($user_id, $coins_pack, $meta_data, $data, $description = 'Начисление за онлайн')
    {
        $this->user_id = $user_id;
        $this->coins_pack = $coins_pack;
        $this->meta_data = $meta_data;
        $this->data = $data;
        $this->description = $description;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $user_id = $this->user_id;
        $user = User::find($user_id);
        $meta_data = $this->meta_data;
        $payments_wallet = $user->getWallet('payments');
        if(!$payments_wallet) {
            $user->createWallet([
                'name' => 'Payments',
                'slug' => 'payments',
                'meta' => ['currency' => 'COINS'],
            ]);
        }
        try {
            $balance = intval($payments_wallet->balance ?? 0);
            $deposit_data = ['get' => 'coins', 'balance_before' => $balance, 'description' => $this->description];
            $deposit_data = array_merge($deposit_data, $this->data);
            $payments_wallet->deposit($this->coins_pack * 50, $deposit_data);
        } catch (\Throwable $e) {
            Log::info(['UpdateUsersBalance error', $e->getMessage()]);
        }
    }
}
