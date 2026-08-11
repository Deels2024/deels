<?php

declare(strict_types=1);

namespace App\Console\Commands\Metrics;

use App\Services\Metrics\MetricsGoogleSheetsService;
use App\Services\Metrics\MetricsReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class GoogleSheetMetrics extends Command
{
    protected $signature = 'metrics:google-sheet
        {type : Report type: daily or weekly}
        {--date= : Daily report date in Y-m-d format, yesterday by default}
        {--to= : Weekly report end date in Y-m-d format, yesterday by default}
        {--retention : Include AppMetrica retention for weekly report}
        {--skip-unavailable-shards : Allow AppMetrica Logs API to return available shards only}
        {--retention-wait : Poll AppMetrica Logs API until retention exports are ready}
        {--retention-wait-attempts=12 : Polling attempts for --retention-wait}
        {--retention-wait-seconds=10 : Seconds between polling attempts for --retention-wait}
        {--repair-template : Restore left-side Google Sheet section and metric labels}
        {--dry-run : Print rows instead of writing to Google Sheets}';

    protected $description = 'Sync metrics report to Google Sheets.';

    public function handle(MetricsReportService $reports, MetricsGoogleSheetsService $sheets): int
    {
        try {
            if ($this->option('repair-template')) {
                if ($this->option('dry-run')) {
                    $this->table(['section', 'metric'], $sheets->templateRows());

                    return self::SUCCESS;
                }

                $result = $sheets->repairTemplate();
                $this->info('Google Sheets template repaired: '.($result['updatedRange'] ?? 'unknown range'));

                return self::SUCCESS;
            }

            $type = strtolower((string) $this->argument('type'));

            if (!in_array($type, ['daily', 'weekly'], true)) {
                $this->error('Report type must be daily or weekly.');

                return self::FAILURE;
            }

            if ($type === 'weekly' && !$this->option('dry-run')) {
                [, $weeklyDateTo] = $reports->weeklyPeriod($this->weeklyDateTo());
                $guard = $sheets->weeklySyncGuard($weeklyDateTo);
                if (($guard['skipped'] ?? false) === true) {
                    $this->warn('Google Sheets metrics sync skipped: '.$guard['reason']);

                    return self::SUCCESS;
                }
            }

            $report = $type === 'daily'
                ? $reports->daily($this->dailyDate())
                : $reports->weekly(
                    $this->weeklyDateTo(),
                    (bool) $this->option('retention'),
                    (bool) $this->option('skip-unavailable-shards'),
                    (bool) $this->option('retention-wait'),
                    (int) $this->option('retention-wait-attempts'),
                    (int) $this->option('retention-wait-seconds')
                );

            if (
                $type === 'weekly'
                && (bool) $this->option('retention')
                && empty($report['retention'])
                && !empty($report['retention_error'])
            ) {
                $this->error('Retention was requested but is unavailable: '.$report['retention_error']);

                return self::FAILURE;
            }

            $rows = $type === 'daily' ? $sheets->dailyRows($report) : $sheets->weeklyRows($report);

            if ($this->option('dry-run')) {
                $this->table(['section', 'metric', 'value'], $rows);
                if ($type === 'daily') {
                    $this->printDailyExplanation($report);
                }

                return self::SUCCESS;
            }

            $result = $type === 'daily' ? $sheets->syncDaily($report) : $sheets->syncWeekly($report);
            if (($result['skipped'] ?? false) === true) {
                $this->warn('Google Sheets metrics sync skipped: '.$result['reason']);

                return self::SUCCESS;
            }

            $this->info('Google Sheets metrics synced: '.($result['updatedRange'] ?? 'unknown range'));

            return self::SUCCESS;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function dailyDate(): Carbon
    {
        return $this->option('date')
            ? Carbon::createFromFormat('Y-m-d', (string) $this->option('date'))->startOfDay()
            : Carbon::yesterday()->startOfDay();
    }

    private function weeklyDateTo(): Carbon
    {
        return $this->option('to')
            ? Carbon::createFromFormat('Y-m-d', (string) $this->option('to'))->endOfDay()
            : Carbon::yesterday()->endOfDay();
    }

    private function printDailyExplanation(array $report): void
    {
        $metrics = $report['metrics'] ?? [];
        $site = $report['site'] ?? [];
        $app = $report['appmetrica'] ?? [];
        $database = $report['database'] ?? [];

        $this->newLine();
        $this->info('Расчет daily-метрик');

        $this->table(['section', 'metric', 'formula', 'values', 'result'], [
            $this->dauExplanation('Общее', $metrics, 'DAU приложения + DAU сайта', 'app='.$this->value($app['dau'] ?? null).', site='.$this->value($site['dau'] ?? null)),
            $this->stickinessExplanation('Общее', $metrics),
            $this->timeExplanation('Общее', $metrics, 'взвешенное среднее по DAU', 'app_time='.$this->value($app['time_on_platform_minutes'] ?? null).', app_dau='.$this->value($app['dau'] ?? null).'; site_time='.$this->value($site['time_on_platform_minutes'] ?? null).', site_dau='.$this->value($site['dau'] ?? null)),
            $this->smsExplanation('Общее', $metrics, "logs.type IN ('sms','sms_web')", 'sms='.$this->value($database['app_messages_count'] ?? null).', sms_web='.$this->value($database['site_messages_count'] ?? null)),
            $this->chattingUsersExplanation('Общее', $metrics, $database),
            $this->dailyDatabaseExplanation('Общее', 'Сред. число взаимодействий', 'interactions / active_stories', 'interactions='.$this->value($database['interactions_count'] ?? null).', active_stories='.$this->value($database['active_stories'] ?? null), $metrics['avg_interactions'] ?? null),
            $this->dailyDatabaseExplanation('Общее', 'Объем внутр. экономики', 'abs(sum(amount_in_rub)) по списаниям пользователей', 'transactions.amount < 0, конвертация в рубли по wallet slug', $metrics['internal_economy'] ?? null),
            $this->dailyDatabaseExplanation('Общее', 'Расход', 'ручной/финансовый источник компании', 'сейчас заглушка', $metrics['expense'] ?? null),
            $this->dailyDatabaseExplanation('Общее', 'Чистая прибыль', 'gross_revenue_day - expense', 'expense='.$this->value($metrics['expense'] ?? null), $metrics['net_profit'] ?? null),

            $this->dauExplanation('Сайт', $site, 'Yandex Metrika ym:s:users', 'filters: ym:s:isRobot==No'),
            $this->stickinessExplanation('Сайт', $site),
            $this->timeExplanation('Сайт', $site, 'ym:s:sumVisitDuration / DAU / 60', 'dau='.$this->value($site['dau'] ?? null).', visits='.$this->value($site['sessions'] ?? null)),
            $this->smsExplanation('Сайт', ['messages_count' => $database['site_messages_count'] ?? null], "logs.type = 'sms_web'", ''),

            $this->dauExplanation('Приложение', $app, 'AppMetrica ym:ge:users', 'application_id='.config('services.appmetrica.application_id')),
            $this->stickinessExplanation('Приложение', $app),
            $this->timeExplanation('Приложение', $app, 'ym:s:sumSessionDuration / DAU / 60', 'dau='.$this->value($app['dau'] ?? null).', sessions='.$this->value($app['sessions'] ?? null).', avg_session_sec='.$this->value($app['avg_session_duration_seconds'] ?? null)),
            $this->smsExplanation('Приложение', ['messages_count' => $database['app_messages_count'] ?? null], "logs.type = 'sms'", ''),
        ]);
    }

    private function dauExplanation(string $section, array $metrics, string $formula, string $values): array
    {
        return [$section, 'DAU', $formula, $values, $this->value($metrics['dau'] ?? null)];
    }

    private function stickinessExplanation(string $section, array $metrics): array
    {
        return [
            $section,
            'Коэф. липкости',
            'DAU / WAU * 100',
            'DAU='.$this->value($metrics['dau'] ?? null).', WAU='.$this->value($metrics['wau'] ?? null),
            $this->percentValue($metrics['stickiness'] ?? null),
        ];
    }

    private function timeExplanation(string $section, array $metrics, string $formula, string $values): array
    {
        return [
            $section,
            'Время на платформе',
            $formula,
            $values,
            $this->minutesValue($metrics['time_on_platform_minutes'] ?? null),
        ];
    }

    private function smsExplanation(string $section, array $metrics, string $formula, string $values): array
    {
        return [$section, 'Число смс за сутки', $formula, $values, $this->value($metrics['messages_count'] ?? null)];
    }

    private function chattingUsersExplanation(string $section, array $metrics, array $database): array
    {
        return [
            $section,
            '% общающихся пользователей',
            'unique messages.user_id / DAU * 100',
            'chat_users='.$this->value($database['chat_users'] ?? null).', DAU='.$this->value($metrics['dau'] ?? null).', user_id != 0',
            $this->percentValue($metrics['chatting_users_percent'] ?? null),
        ];
    }

    private function dailyDatabaseExplanation(string $section, string $metric, string $formula, string $values, mixed $result): array
    {
        return [$section, $metric, $formula, $values, $this->value($result)];
    }

    private function value(mixed $value): string
    {
        return $value === null ? 'н/д' : (string) $value;
    }

    private function percentValue(mixed $value): string
    {
        return $value === null ? 'н/д' : $value.'%';
    }

    private function minutesValue(mixed $value): string
    {
        return $value === null ? 'н/д' : $value.' мин.';
    }
}
