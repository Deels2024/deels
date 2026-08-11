<?php

declare(strict_types=1);

namespace App\Console\Commands\Metrics;

use App\Services\Metrics\MetricsReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class DailyMetrics extends Command
{
    protected $signature = 'metrics:daily {--date= : Report date in Y-m-d format, yesterday by default}';

    protected $description = 'Preview daily metrics report.';

    public function handle(MetricsReportService $reports): int
    {
        $date = $this->option('date')
            ? Carbon::createFromFormat('Y-m-d', (string) $this->option('date'))->startOfDay()
            : Carbon::yesterday()->startOfDay();

        try {
            $report = $reports->daily($date);
            $metrics = $report['metrics'];

            $this->info('Daily metrics: '.$report['period']['date_from']);
            $this->table(['metric', 'value'], [
                ['DAU', $metrics['dau']],
                ['WAU', $metrics['wau']],
                ['Stickiness, %', $this->formatNullable($metrics['stickiness'])],
                ['Time on platform, min/user', $this->formatNullable($metrics['time_on_platform_minutes'])],
                ['Sessions', $metrics['sessions']],
                ['Avg session, sec', $this->formatNullable($metrics['avg_session_duration_seconds'])],
                ['Avg interactions', $this->formatNullable($metrics['avg_interactions'])],
                ['Messages', $metrics['messages_count']],
                ['Chatting users, %', $this->formatNullable($metrics['chatting_users_percent'])],
                ['Internal economy, rub', $metrics['internal_economy']],
                ['Expense, rub', $metrics['expense']],
                ['Net profit, rub', $metrics['net_profit']],
            ]);

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
