<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Helpers\AppHelper;
use App\Jobs\Mail\ParseIncomeMails;
use App\Models\User;
use Illuminate\Console\Command;
use Carbon\Carbon;

class DeleteNotActivatedUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:notactive:delete';

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
        $telegram = new AppHelper();
        $users = User::where('created_at', '<', Carbon::now()->subHours(24))
            ->where('last_active', '<', Carbon::now()->subHours(24))
            ->where('is_activated', false)
            ->whereHas('phoneVerify', function ($query) {

            })
            ->count();
//        $telegram->telegram_message('Удалено неактивных пользователей: ' . $users);
        $users = User::where('created_at', '<', Carbon::now()->subHours(24))
            ->where('last_active', '<', Carbon::now()->subHours(24))
            ->where('is_activated', false)
            ->whereHas('phoneVerify', function ($query) {

            })
            ->forceDelete();
    }
}
