<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Helpers\AppHelper;
use App\Models\Payment;
use App\Models\Thanks;
use Illuminate\Console\Command;
use Intervention\Image\Facades\Image;

class TestCronSchedule extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:test:schedule';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test cron schedule worker';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $telegram = new AppHelper();
        $telegram->telegram_message('test');
    }
}
