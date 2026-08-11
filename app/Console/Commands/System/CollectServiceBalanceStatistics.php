<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Services\ServiceBalanceStatisticsService;
use Illuminate\Console\Command;

class CollectServiceBalanceStatistics extends Command
{
    protected $signature = 'services:balances:collect';

    protected $description = 'Collect external service balance statistics';

    public function handle(ServiceBalanceStatisticsService $service): int
    {
        $statistic = $service->collect();

        $this->info('Service balance statistics collected: '.$statistic->id);

        return self::SUCCESS;
    }
}
