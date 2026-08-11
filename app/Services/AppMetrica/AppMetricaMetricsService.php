<?php

declare(strict_types=1);

namespace App\Services\AppMetrica;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;

class AppMetricaMetricsService
{
    public const METRIC_USERS = 'ym:ge:users';

    public const METRIC_GENERAL_SESSIONS = 'ym:ge:sessions';

    public const METRIC_SESSION_SESSIONS = 'ym:s:sessions';

    public const METRIC_AVG_SESSION_DURATION = 'ym:s:avgSessionDuration';

    public const METRIC_SUM_SESSION_DURATION = 'ym:s:sumSessionDuration';

    public const DIMENSION_OPERATING_SYSTEM = 'ym:ge:operatingSystem';

    public const DIMENSION_OPERATING_SYSTEM_NAME = 'ym:ge:operatingSystemName';

    public const DIMENSION_DEVICE_TYPE = 'ym:ge:deviceType';

    public function __construct(private AppMetricaClient $client)
    {
    }

    /**
     * Active users for one day.
     *
     * @throws GuzzleException
     */
    public function dau(Carbon $date): int
    {
        return (int) round($this->singleMetric(
            $date,
            $date,
            self::METRIC_USERS
        ));
    }

    /**
     * Active users for a 7-day window ending at $date.
     *
     * @throws GuzzleException
     */
    public function wau(Carbon $date): int
    {
        return (int) round($this->singleMetric(
            $date->copy()->subDays(6),
            $date,
            self::METRIC_USERS
        ));
    }

    /**
     * DAU / WAU * 100 for the day.
     *
     * @throws GuzzleException
     */
    public function stickiness(Carbon $date): ?float
    {
        $wau = $this->wau($date);

        if ($wau === 0) {
            return null;
        }

        return round($this->dau($date) / $wau * 100, 2);
    }

    /**
     * Number of foreground sessions for one day.
     *
     * @throws GuzzleException
     */
    public function sessions(Carbon $date): int
    {
        return (int) round($this->singleMetric(
            $date,
            $date,
            self::METRIC_SESSION_SESSIONS
        ));
    }

    /**
     * Average session duration for one day, in seconds.
     *
     * @throws GuzzleException
     */
    public function averageSessionDurationSeconds(Carbon $date): ?float
    {
        return $this->singleMetric(
            $date,
            $date,
            self::METRIC_AVG_SESSION_DURATION
        );
    }

    /**
     * Total foreground session duration for one day, in seconds.
     *
     * @throws GuzzleException
     */
    public function totalSessionDurationSeconds(Carbon $date): ?float
    {
        return $this->singleMetric(
            $date,
            $date,
            self::METRIC_SUM_SESSION_DURATION
        );
    }

    /**
     * Average time on platform per active user, in minutes.
     *
     * @throws GuzzleException
     */
    public function timeOnPlatformMinutes(Carbon $date): ?float
    {
        $dau = $this->dau($date);

        if ($dau === 0) {
            return null;
        }

        $seconds = $this->totalSessionDurationSeconds($date);

        return $seconds === null ? null : round($seconds / $dau / 60, 2);
    }

    /**
     * DAU grouped by operating system.
     *
     * @throws GuzzleException
     */
    public function dauByOperatingSystem(Carbon $date): array
    {
        return $this->dimensionReport(
            $date,
            $date,
            self::METRIC_USERS,
            self::DIMENSION_OPERATING_SYSTEM
        );
    }

    /**
     * @throws GuzzleException
     */
    public function dailySummary(Carbon $date): array
    {
        $dau = $this->dau($date);
        $wau = $this->wau($date);

        return [
            'date' => $date->toDateString(),
            'dau' => $dau,
            'wau' => $wau,
            'stickiness' => $wau === 0 ? null : round($dau / $wau * 100, 2),
            'sessions' => $this->sessions($date),
            'avg_session_duration_seconds' => $this->averageSessionDurationSeconds($date),
            'time_on_platform_minutes' => $dau === 0 ? null : $this->timeOnPlatformMinutes($date),
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function singleMetric(Carbon $dateFrom, Carbon $dateTo, string $metric, array $params = []): ?float
    {
        $response = $this->client->report(array_merge($params, [
            'date1' => $dateFrom->toDateString(),
            'date2' => $dateTo->toDateString(),
            'metrics' => $metric,
        ]));

        return $this->firstTotal($response);
    }

    /**
     * Run a raw Reporting API request. Use this for copied API queries from AppMetrica UI.
     *
     * @throws GuzzleException
     */
    public function rawReport(array $params): array
    {
        return $this->client->report($params);
    }

    /**
     * Placeholder for Retention/Cohort report queries copied from AppMetrica UI.
     *
     * Retention report API parameters differ from ordinary audience metrics and
     * should be supplied from "Copy table API request" in the AppMetrica report.
     *
     * @throws GuzzleException
     */
    public function retentionReport(array $copiedReportParams): array
    {
        return $this->rawReport($copiedReportParams);
    }

    /**
     * @throws GuzzleException
     */
    public function dimensionReport(Carbon $dateFrom, Carbon $dateTo, string $metric, string $dimension, array $params = []): array
    {
        $response = $this->client->report(array_merge($params, [
            'date1' => $dateFrom->toDateString(),
            'date2' => $dateTo->toDateString(),
            'metrics' => $metric,
            'dimensions' => $dimension,
        ]));

        return $this->rowsByFirstDimension($response);
    }

    private function firstTotal(array $response): ?float
    {
        $value = $response['totals'][0] ?? null;

        return is_numeric($value) ? (float) $value : null;
    }

    private function rowsByFirstDimension(array $response): array
    {
        $result = [];

        foreach ($response['data'] ?? [] as $row) {
            $name = $row['dimensions'][0]['name'] ?? null;
            $value = $row['metrics'][0] ?? null;

            if ($name !== null && is_numeric($value)) {
                $result[$name] = (float) $value;
            }
        }

        return $result;
    }
}
