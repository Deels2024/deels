<?php

declare(strict_types=1);

namespace App\Console\Commands\Metrics;

use App\Services\Metrics\MetricsReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class WeeklyMetrics extends Command
{
    protected $signature = 'metrics:weekly
        {--to= : Report end date in Y-m-d format, yesterday by default}
        {--retention : Include AppMetrica retention via Logs API}
        {--skip-unavailable-shards : Allow AppMetrica Logs API to return available shards only}';

    protected $description = 'Preview weekly metrics report.';

    public function handle(MetricsReportService $reports): int
    {
        $dateTo = $this->option('to')
            ? Carbon::createFromFormat('Y-m-d', (string) $this->option('to'))->endOfDay()
            : Carbon::yesterday()->endOfDay();

        try {
            $report = $reports->weekly(
                $dateTo,
                (bool) $this->option('retention'),
                (bool) $this->option('skip-unavailable-shards')
            );
            $metrics = $report['metrics'];

            $this->info(sprintf(
                'Weekly metrics: %s - %s',
                $report['period']['date_from'],
                $report['period']['date_to']
            ));
            $this->table(['metric', 'value'], [
                ['DAU (end date)', $metrics['dau']],
                ['WAU', $metrics['wau']],
                ['Stickiness, %', $this->formatNullable($metrics['stickiness'])],
                ['Gross revenue, rub', $metrics['gross_revenue']],
                ['Profitability, %', $this->formatNullable($metrics['profitability_percent'])],
                ['Participation rate', $this->formatNullable($metrics['participation_rate'])],
                ['Active creators, %', $this->formatNullable($metrics['active_creators_percent'])],
                ['Regular creators, %', $this->formatNullable($metrics['regular_creators_percent'])],
                ['Virality rate', $this->formatNullable($metrics['virality_rate'])],
                ['Profit per user, rub', $this->formatNullable($metrics['profit_per_user'])],
            ]);

            if ($report['retention']) {
                $rows = [];
                foreach ($report['retention']['retention'] as $day => $data) {
                    $rows[] = [$day.'d', $data['base'], $data['returned'], $this->formatNullable($data['rate'])];
                }
                $this->info('Retention');
                $this->table(['day', 'base', 'returned', 'retention %'], $rows);
            } elseif ($report['retention_error']) {
                $this->warn('Retention skipped: '.$report['retention_error']);
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function formatNullable(mixed $value): string
    {
        return $value === null ? 'n/a' : (string) $value;
    }
}
