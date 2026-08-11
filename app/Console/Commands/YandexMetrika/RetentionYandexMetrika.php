<?php

declare(strict_types=1);

namespace App\Console\Commands\YandexMetrika;

use App\Services\YandexMetrika\YandexMetrikaRetentionService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class RetentionYandexMetrika extends Command
{
    protected $signature = 'yandex-metrika:retention
        {--from= : Week/report start date in Y-m-d format}
        {--to= : Week/report end date in Y-m-d format}
        {--wait : Poll Logs API until prepared exports are ready}
        {--wait-attempts=12 : Polling attempts for --wait}
        {--wait-seconds=10 : Seconds between polling attempts for --wait}';

    protected $description = 'Calculate Yandex Metrika site cohort retention for 1/3/7/30 days using Logs API visits.';

    public function handle(YandexMetrikaRetentionService $retention): int
    {
        [$dateFrom, $dateTo] = $this->period();

        $this->info('Checking Yandex Metrika Logs API retention...');
        $this->line(sprintf('Period: %s - %s', $dateFrom->toDateString(), $dateTo->toDateString()));

        try {
            $report = $retention->weeklyRetention(
                $dateFrom,
                $dateTo,
                (bool) $this->option('wait'),
                (int) $this->option('wait-attempts'),
                (int) $this->option('wait-seconds')
            );

            $this->line('Log request ID: '.($report['log_request_id'] ?? '<unknown>'));

            $rows = [];
            foreach ($report['retention'] as $day => $data) {
                $rows[] = [
                    $day.'d',
                    $data['base'],
                    $data['returned'],
                    $data['rate'] === null ? 'n/a' : $data['rate'].'%',
                ];
            }

            $this->table(['day', 'base', 'returned', 'retention'], $rows);

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function period(): array
    {
        if ($this->option('from') && $this->option('to')) {
            return [
                Carbon::createFromFormat('Y-m-d', (string) $this->option('from'))->startOfDay(),
                Carbon::createFromFormat('Y-m-d', (string) $this->option('to'))->endOfDay(),
            ];
        }

        $end = Carbon::yesterday()->endOfDay();

        return [$end->copy()->subDays(6)->startOfDay(), $end];
    }
}
