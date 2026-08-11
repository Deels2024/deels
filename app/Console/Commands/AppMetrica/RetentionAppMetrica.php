<?php

declare(strict_types=1);

namespace App\Console\Commands\AppMetrica;

use App\Services\AppMetrica\AppMetricaRetentionService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use RuntimeException;
use Throwable;

class RetentionAppMetrica extends Command
{
    protected $signature = 'appmetrica:retention
        {--from= : Week/report start date in Y-m-d format}
        {--to= : Week/report end date in Y-m-d format}
        {--previous-week : Use previous Monday-Sunday week}
        {--skip-unavailable-shards : Allow Logs API to return data from available shards only}
        {--wait : Poll Logs API until prepared exports are ready}
        {--wait-attempts=12 : Polling attempts for --wait}
        {--wait-seconds=10 : Seconds between polling attempts for --wait}';

    protected $description = 'Calculate AppMetrica cohort retention for 1/3/7/30 days using Logs API.';

    public function handle(AppMetricaRetentionService $retention): int
    {
        [$dateFrom, $dateTo] = $this->period();

        $this->info('Checking AppMetrica Logs API...');
        $this->line(sprintf('Period: %s - %s', $dateFrom->toDateString(), $dateTo->toDateString()));

        try {
            $status = $retention->logsApiStatus();
            $this->line('Logs API status: '.($status['logs_api_availability_status'] ?? 'unknown'));

            $retention->skipUnavailableShards((bool) $this->option('skip-unavailable-shards'));

            $report = $this->option('wait')
                ? $this->waitForReport($retention, $dateFrom, $dateTo)
                : $retention->weeklyRetention($dateFrom, $dateTo);

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
        if ($this->option('previous-week')) {
            $start = Carbon::now()->startOfWeek()->subWeek();

            return [$start, $start->copy()->endOfWeek()];
        }

        if ($this->option('from') && $this->option('to')) {
            return [
                Carbon::createFromFormat('Y-m-d', (string) $this->option('from'))->startOfDay(),
                Carbon::createFromFormat('Y-m-d', (string) $this->option('to'))->endOfDay(),
            ];
        }

        $end = Carbon::yesterday()->endOfDay();

        return [$end->copy()->subDays(6)->startOfDay(), $end];
    }

    private function waitForReport(AppMetricaRetentionService $retention, Carbon $dateFrom, Carbon $dateTo): array
    {
        $attempts = max(1, (int) $this->option('wait-attempts'));
        $seconds = max(1, (int) $this->option('wait-seconds'));
        $lastException = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                if ($attempt > 1) {
                    $this->line(sprintf('Polling Logs API, attempt %d/%d...', $attempt, $attempts));
                }

                return $retention->weeklyRetention($dateFrom, $dateTo);
            } catch (RuntimeException $e) {
                $lastException = $e;

                if (!str_contains($e->getMessage(), 'is being prepared') || $attempt === $attempts) {
                    throw $e;
                }

                $this->line($e->getMessage());
                sleep($seconds);
            }
        }

        throw $lastException ?: new RuntimeException('Unable to load retention report.');
    }
}
