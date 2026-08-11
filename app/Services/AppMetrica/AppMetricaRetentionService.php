<?php

declare(strict_types=1);

namespace App\Services\AppMetrica;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use RuntimeException;

class AppMetricaRetentionService
{
    private const RETENTION_DAYS = [1, 3, 7, 30];

    private bool $skipUnavailableShards = false;

    public function __construct(private AppMetricaClient $client)
    {
    }

    public function skipUnavailableShards(bool $skipUnavailableShards = true): self
    {
        $this->skipUnavailableShards = $skipUnavailableShards;

        return $this;
    }

    /**
     * Calculates retention for users whose Nth day falls inside the report period.
     *
     * @throws GuzzleException
     */
    public function weeklyRetention(Carbon $weekStart, Carbon $weekEnd): array
    {
        $weekStart = $weekStart->copy()->startOfDay();
        $weekEnd = $weekEnd->copy()->endOfDay();

        $installFrom = $weekStart->copy()->subDays(max(self::RETENTION_DAYS))->startOfDay();
        $installTo = $weekEnd->copy()->subDay()->endOfDay();

        $installations = $this->loadInstallations($installFrom, $installTo);
        $sessions = $this->loadSessions($weekStart, $weekEnd);

        $sessionsByDeviceAndDate = $this->indexSessionsByDeviceAndDate($sessions);
        $installationsByDueDay = $this->indexInstallationsByDueDay($installations, $weekStart, $weekEnd);

        $result = [];
        foreach (self::RETENTION_DAYS as $day) {
            $base = 0;
            $returned = 0;

            foreach ($installationsByDueDay[$day] ?? [] as $installation) {
                $base++;

                $deviceId = $installation['appmetrica_device_id'];
                $returnDate = Carbon::parse($installation['install_datetime'])->addDays($day)->toDateString();

                if (isset($sessionsByDeviceAndDate[$deviceId][$returnDate])) {
                    $returned++;
                }
            }

            $result[$day] = [
                'base' => $base,
                'returned' => $returned,
                'rate' => $base === 0 ? null : round($returned / $base * 100, 2),
            ];
        }

        return [
            'period' => [
                'date_from' => $weekStart->toDateString(),
                'date_to' => $weekEnd->toDateString(),
            ],
            'retention' => $result,
        ];
    }

    /**
     * @throws GuzzleException
     */
    public function logsApiStatus(): array
    {
        return $this->client->logsApiStatus();
    }

    /**
     * @throws GuzzleException
     */
    private function loadInstallations(Carbon $dateFrom, Carbon $dateTo): array
    {
        $response = $this->client->logsExport('installations', [
            'date_since' => $dateFrom->format('Y-m-d H:i:s'),
            'date_until' => $dateTo->format('Y-m-d H:i:s'),
            'date_dimension' => 'default',
            'fields' => 'appmetrica_device_id,install_datetime,os_name',
            'skip_unavailable_shards' => $this->skipUnavailableShards ? 'true' : 'false',
        ]);

        return $this->readyRows($response, 'installations');
    }

    /**
     * @throws GuzzleException
     */
    private function loadSessions(Carbon $dateFrom, Carbon $dateTo): array
    {
        $response = $this->client->logsExport('sessions_starts', [
            'date_since' => $dateFrom->format('Y-m-d H:i:s'),
            'date_until' => $dateTo->format('Y-m-d H:i:s'),
            'date_dimension' => 'default',
            'fields' => 'appmetrica_device_id,session_start_datetime,os_name',
            'skip_unavailable_shards' => $this->skipUnavailableShards ? 'true' : 'false',
        ]);

        return $this->readyRows($response, 'sessions_starts');
    }

    private function readyRows(array $response, string $resource): array
    {
        if ($response['status'] === 202) {
            throw new RuntimeException(sprintf(
                'Logs API export for %s is being prepared. Run the command again in a few minutes.',
                $resource
            ));
        }

        if ($response['status'] !== 200) {
            throw new RuntimeException(sprintf(
                'Logs API export for %s failed with HTTP %s: %s',
                $resource,
                $response['status'],
                $response['body']
            ));
        }

        return $response['json'] ?? [];
    }

    private function indexSessionsByDeviceAndDate(array $sessions): array
    {
        $index = [];

        foreach ($sessions as $session) {
            $deviceId = $session['appmetrica_device_id'] ?? null;
            $dateTime = $session['session_start_datetime'] ?? null;

            if (!$deviceId || !$dateTime) {
                continue;
            }

            $index[$deviceId][Carbon::parse($dateTime)->toDateString()] = true;
        }

        return $index;
    }

    private function indexInstallationsByDueDay(array $installations, Carbon $weekStart, Carbon $weekEnd): array
    {
        $index = array_fill_keys(self::RETENTION_DAYS, []);

        foreach ($installations as $installation) {
            if (empty($installation['appmetrica_device_id']) || empty($installation['install_datetime'])) {
                continue;
            }

            $installDate = Carbon::parse($installation['install_datetime'])->startOfDay();

            foreach (self::RETENTION_DAYS as $day) {
                $dueDate = $installDate->copy()->addDays($day);

                if ($dueDate->betweenIncluded($weekStart, $weekEnd)) {
                    $index[$day][] = $installation;
                }
            }
        }

        return $index;
    }
}
