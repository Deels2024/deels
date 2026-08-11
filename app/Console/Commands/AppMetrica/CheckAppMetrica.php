<?php

declare(strict_types=1);

namespace App\Console\Commands\AppMetrica;

use App\Services\AppMetrica\AppMetricaClient;
use App\Services\AppMetrica\AppMetricaMetricsService;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class CheckAppMetrica extends Command
{
    protected $signature = 'appmetrica:check {--date= : Date to check in Y-m-d format, yesterday by default}';

    protected $description = 'Check AppMetrica OAuth credentials, application access, and a basic Reporting API request.';

    public function handle(AppMetricaClient $client, AppMetricaMetricsService $metrics): int
    {
        $applicationId = config('services.appmetrica.application_id');
        $date = $this->option('date')
            ? Carbon::createFromFormat('Y-m-d', (string) $this->option('date'))->startOfDay()
            : Carbon::yesterday()->startOfDay();

        $this->info('Checking AppMetrica connection...');
        $this->line('Application ID: '.($applicationId ?: '<not configured>'));
        $this->line('Date: '.$date->toDateString());

        try {
            $applications = $client->applications();
            $items = $applications['applications'] ?? [];

            $this->info('Management API: OK, applications available: '.count($items));

            foreach ($items as $item) {
                $this->line(sprintf(
                    '- %s (id: %s, permission: %s)',
                    $item['name'] ?? '<unnamed>',
                    $item['id'] ?? '<unknown>',
                    $item['permission'] ?? '<unknown>'
                ));
            }

            if ($applicationId && !$this->hasApplication($items, (int) $applicationId)) {
                $this->warn('Configured APPMETRICA_APPLICATION_ID was not found in the available applications list.');
            }

            $summary = $metrics->dailySummary($date);

            $this->info('Reporting API: OK');
            $this->table(
                ['date', 'dau', 'wau', 'stickiness', 'sessions', 'avg session sec', 'time/user min'],
                [[
                    $summary['date'],
                    $summary['dau'],
                    $summary['wau'],
                    $summary['stickiness'] === null ? 'n/a' : $summary['stickiness'].'%',
                    $summary['sessions'],
                    $summary['avg_session_duration_seconds'] === null ? 'n/a' : round($summary['avg_session_duration_seconds'], 2),
                    $summary['time_on_platform_minutes'] === null ? 'n/a' : $summary['time_on_platform_minutes'],
                ]]
            );

            $byOs = $metrics->dauByOperatingSystem($date);
            if ($byOs) {
                $this->info('DAU by operating system');
                $this->table(
                    ['operating system', 'users'],
                    array_map(
                        static fn (string $name, float $value): array => [$name, (int) round($value)],
                        array_keys($byOs),
                        $byOs
                    )
                );
            }

            return self::SUCCESS;
        } catch (RequestException $e) {
            $this->error('AppMetrica request failed: '.$e->getMessage());

            $response = $e->getResponse();
            if ($response) {
                $this->line((string) $response->getBody());
            }

            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }

    private function hasApplication(array $applications, int $applicationId): bool
    {
        foreach ($applications as $application) {
            if ((int) ($application['id'] ?? 0) === $applicationId) {
                return true;
            }
        }

        return false;
    }
}
