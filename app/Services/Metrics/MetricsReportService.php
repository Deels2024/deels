<?php

declare(strict_types=1);

namespace App\Services\Metrics;

use App\Services\AppMetrica\AppMetricaMetricsService;
use App\Services\AppMetrica\AppMetricaRetentionService;
use App\Services\YandexMetrika\YandexMetrikaMetricsService;
use App\Services\YandexMetrika\YandexMetrikaRetentionService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use RuntimeException;

class MetricsReportService
{
    public function __construct(
        private AppMetricaMetricsService $appMetrica,
        private AppMetricaRetentionService $retention,
        private DatabaseMetricsService $databaseMetrics,
        private YandexMetrikaMetricsService $yandexMetrika,
        private YandexMetrikaRetentionService $yandexRetention
    ) {
    }

    /**
     * @throws GuzzleException
     */
    public function daily(Carbon $date): array
    {
        $date = $date->copy()->startOfDay();
        $app = $this->appMetrica->dailySummary($date);
        $site = $this->yandexMetrika->dailySummary($date);
        $analytics = $this->combinedAnalytics($app, $site);
        $db = $this->databaseMetrics->daily(
            $date->copy()->startOfDay(),
            $date->copy()->endOfDay(),
            (int) ($analytics['dau'] ?? 0)
        );

        return [
            'period' => [
                'date_from' => $date->toDateString(),
                'date_to' => $date->toDateString(),
            ],
            'appmetrica' => $app,
            'site' => $site,
            'database' => $db,
            'metrics' => array_merge($analytics, $db),
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function weekly(
        Carbon $dateTo,
        bool $withRetention = false,
        bool $skipUnavailableShards = false,
        bool $waitForRetention = false,
        int $retentionWaitAttempts = 12,
        int $retentionWaitSeconds = 10
    ): array {
        [$dateFrom, $dateTo] = $this->weeklyPeriod($dateTo);
        $app = $this->appMetrica->dailySummary($dateTo->copy()->startOfDay());
        $site = $this->yandexMetrika->dailySummary($dateTo->copy()->startOfDay());
        $analytics = $this->combinedAnalytics($app, $site);
        $dailyDb = $this->databaseMetrics->daily(
            $dateTo->copy()->startOfDay(),
            $dateTo->copy()->endOfDay(),
            (int) ($analytics['dau'] ?? 0)
        );
        $weeklyDb = $this->databaseMetrics->weekly($dateFrom, $dateTo, (int) ($analytics['wau'] ?? 0));
        $retention = null;
        $retentionError = null;
        $appRetention = null;
        $siteRetention = null;
        $siteRetentionError = null;

        if ($withRetention) {
            try {
                $this->retention->skipUnavailableShards($skipUnavailableShards);
                $appRetention = $waitForRetention
                    ? $this->waitForRetention($dateFrom, $dateTo, $retentionWaitAttempts, $retentionWaitSeconds)
                    : $this->retention->weeklyRetention($dateFrom, $dateTo);
            } catch (RuntimeException $e) {
                $retentionError = $e->getMessage();
            }

            try {
                $siteRetention = $this->yandexRetention->weeklyRetention(
                    $dateFrom,
                    $dateTo,
                    $waitForRetention,
                    $retentionWaitAttempts,
                    $retentionWaitSeconds
                );
            } catch (RuntimeException $e) {
                $siteRetentionError = $e->getMessage();
            }

            $retention = $this->combinedRetention($appRetention, $siteRetention);
        }

        return [
            'period' => [
                'date_from' => $dateFrom->toDateString(),
                'date_to' => $dateTo->toDateString(),
            ],
            'appmetrica' => $app,
            'site' => $site,
            'database_daily' => $dailyDb,
            'database_weekly' => $weeklyDb,
            'retention' => $retention,
            'app_retention' => $appRetention,
            'site_retention' => $siteRetention,
            'retention_error' => $retentionError,
            'site_retention_error' => $siteRetentionError,
            'metrics' => array_merge($analytics, $dailyDb, $weeklyDb),
        ];
    }

    private function combinedAnalytics(array $app, array $site): array
    {
        $dau = $this->sumValues($app['dau'] ?? null, $site['dau'] ?? null);
        $wau = $this->sumValues($app['wau'] ?? null, $site['wau'] ?? null);

        return array_merge($app, [
            'dau' => $dau,
            'wau' => $wau,
            'sessions' => $this->sumValues($app['sessions'] ?? null, $site['sessions'] ?? null),
            'stickiness' => $wau > 0 ? round($dau / $wau * 100, 2) : null,
            'time_on_platform_minutes' => $this->weightedAverage(
                $app['time_on_platform_minutes'] ?? null,
                $app['dau'] ?? null,
                $site['time_on_platform_minutes'] ?? null,
                $site['dau'] ?? null
            ),
        ]);
    }

    private function sumValues(mixed ...$values): float
    {
        $sum = 0.0;

        foreach ($values as $value) {
            if (is_numeric($value)) {
                $sum += (float) $value;
            }
        }

        return $sum;
    }

    private function weightedAverage(mixed $leftValue, mixed $leftWeight, mixed $rightValue, mixed $rightWeight): ?float
    {
        $sum = 0.0;
        $weight = 0.0;

        if (is_numeric($leftValue) && is_numeric($leftWeight) && (float) $leftWeight > 0) {
            $sum += (float) $leftValue * (float) $leftWeight;
            $weight += (float) $leftWeight;
        }

        if (is_numeric($rightValue) && is_numeric($rightWeight) && (float) $rightWeight > 0) {
            $sum += (float) $rightValue * (float) $rightWeight;
            $weight += (float) $rightWeight;
        }

        return $weight > 0 ? round($sum / $weight, 2) : null;
    }

    private function combinedRetention(?array ...$reports): ?array
    {
        $availableReports = array_values(array_filter($reports));
        if ($availableReports === []) {
            return null;
        }

        $combined = [];

        foreach ([1, 3, 7, 30] as $day) {
            $base = 0;
            $returned = 0;

            foreach ($availableReports as $report) {
                if (empty($report['retention'][$day])) {
                    continue;
                }

                $base += (int) ($report['retention'][$day]['base'] ?? 0);
                $returned += (int) ($report['retention'][$day]['returned'] ?? 0);
            }

            $combined[$day] = [
                'base' => $base,
                'returned' => $returned,
                'rate' => $base === 0 ? null : round($returned / $base * 100, 2),
            ];
        }

        return [
            'retention' => $combined,
        ];
    }

    private function waitForRetention(Carbon $dateFrom, Carbon $dateTo, int $attempts, int $seconds): array
    {
        $attempts = max(1, $attempts);
        $seconds = max(1, $seconds);
        $lastException = null;

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            try {
                return $this->retention->weeklyRetention($dateFrom, $dateTo);
            } catch (RuntimeException $e) {
                $lastException = $e;

                if (!str_contains($e->getMessage(), 'is being prepared') || $attempt === $attempts) {
                    throw $e;
                }

                sleep($seconds);
            }
        }

        throw $lastException ?: new RuntimeException('Unable to load retention report.');
    }

    public function weeklyPeriod(Carbon $dateTo): array
    {
        $endSunday = $dateTo->copy()->endOfDay();
        if (!$endSunday->isSunday()) {
            $endSunday = $endSunday->previous(Carbon::SUNDAY)->endOfDay();
        }

        return [
            $endSunday->copy()->subWeek()->endOfDay(),
            $endSunday,
        ];
    }
}
