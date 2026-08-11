<?php

declare(strict_types=1);

namespace App\Services\YandexMetrika;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;

class YandexMetrikaMetricsService
{
    private const METRIC_USERS = 'ym:s:users';

    private const METRIC_VISITS = 'ym:s:visits';

    private const METRIC_SUM_VISIT_DURATION = 'ym:s:sumVisitDuration';

    private const FILTER_NO_ROBOTS = "ym:s:isRobot=='No'";

    public function __construct(private YandexMetrikaClient $client)
    {
    }

    /**
     * @throws GuzzleException
     */
    public function dailySummary(Carbon $date): array
    {
        $dau = $this->users($date, $date);
        $wau = $this->users($date->copy()->subDays(6), $date);
        $duration = $this->singleMetric($date, $date, self::METRIC_SUM_VISIT_DURATION);

        return [
            'date' => $date->toDateString(),
            'dau' => $dau,
            'wau' => $wau,
            'stickiness' => $wau === 0 ? null : round($dau / $wau * 100, 2),
            'sessions' => $this->visits($date, $date),
            'avg_session_duration_seconds' => null,
            'time_on_platform_minutes' => $dau === 0 || $duration === null ? null : round($duration / $dau / 60, 2),
            'avg_interactions' => null,
            'messages_count' => null,
            'chatting_users_percent' => null,
            'internal_economy' => null,
            'expense' => null,
            'net_profit' => null,
            'virality_rate' => null,
            'participation_rate' => null,
            'active_creators_percent' => null,
            'regular_creators_percent' => null,
            'gross_revenue' => null,
            'profitability_percent' => null,
            'profit_per_user' => null,
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function users(Carbon $dateFrom, Carbon $dateTo): int
    {
        return (int) round($this->singleMetric($dateFrom, $dateTo, self::METRIC_USERS) ?? 0);
    }

    /**
     * @throws GuzzleException
     */
    public function visits(Carbon $dateFrom, Carbon $dateTo): int
    {
        return (int) round($this->singleMetric($dateFrom, $dateTo, self::METRIC_VISITS) ?? 0);
    }

    /**
     * @throws GuzzleException
     */
    public function singleMetric(Carbon $dateFrom, Carbon $dateTo, string $metric, array $params = []): ?float
    {
        $response = $this->client->report(array_merge([
            'date1' => $dateFrom->toDateString(),
            'date2' => $dateTo->toDateString(),
            'metrics' => $metric,
            'filters' => self::FILTER_NO_ROBOTS,
        ], $params));

        $value = $response['totals'][0] ?? null;

        return is_numeric($value) ? (float) $value : null;
    }
}
