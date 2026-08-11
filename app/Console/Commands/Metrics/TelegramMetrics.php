<?php

declare(strict_types=1);

namespace App\Console\Commands\Metrics;

use App\Helpers\AppHelper;
use App\Services\Metrics\MetricsReportService;
use App\Services\Metrics\MetricsTelegramFormatter;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class TelegramMetrics extends Command
{
    protected $signature = 'metrics:telegram
        {type : Report type: daily or weekly}
        {--date= : Daily report date in Y-m-d format, yesterday by default}
        {--to= : Weekly report end date in Y-m-d format, yesterday by default}
        {--retention : Include AppMetrica retention for weekly report}
        {--skip-unavailable-shards : Allow AppMetrica Logs API to return available shards only}
        {--retention-wait : Poll AppMetrica Logs API until retention exports are ready}
        {--retention-wait-attempts=12 : Polling attempts for --retention-wait}
        {--retention-wait-seconds=10 : Seconds between polling attempts for --retention-wait}
        {--chat-id= : Telegram chat id, default from AppHelper}
        {--dry-run : Print message instead of sending it}';

    protected $description = 'Send or preview metrics report in Telegram format.';

    public function handle(
        MetricsReportService $reports,
        MetricsTelegramFormatter $formatter,
        AppHelper $telegram
    ): int {
        try {
            $type = strtolower((string) $this->argument('type'));

            if (!in_array($type, ['daily', 'weekly'], true)) {
                $this->error('Report type must be daily or weekly.');

                return self::FAILURE;
            }

            $message = $type === 'daily'
                ? $this->dailyMessage($reports, $formatter)
                : $this->weeklyMessage($reports, $formatter);

            if ($this->option('dry-run')) {
                $this->line($message);

                return self::SUCCESS;
            }

            $chatId = $this->option('chat-id') ? (string) $this->option('chat-id') : null;
            $telegram->telegram_group_message($message, $chatId);
            $this->info('Telegram metrics report sent.');

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function dailyMessage(MetricsReportService $reports, MetricsTelegramFormatter $formatter): string
    {
        $date = $this->option('date')
            ? Carbon::createFromFormat('Y-m-d', (string) $this->option('date'))->startOfDay()
            : Carbon::yesterday()->startOfDay();

        return $formatter->daily($reports->daily($date));
    }

    private function weeklyMessage(MetricsReportService $reports, MetricsTelegramFormatter $formatter): string
    {
        $dateTo = $this->option('to')
            ? Carbon::createFromFormat('Y-m-d', (string) $this->option('to'))->endOfDay()
            : Carbon::yesterday()->endOfDay();

        return $formatter->weekly($reports->weekly(
            $dateTo,
            (bool) $this->option('retention'),
            (bool) $this->option('skip-unavailable-shards'),
            (bool) $this->option('retention-wait'),
            (int) $this->option('retention-wait-attempts'),
            (int) $this->option('retention-wait-seconds')
        ));
    }
}
