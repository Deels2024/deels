<?php

declare(strict_types=1);

namespace App\Services\YandexMetrika;

use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Carbon;
use RuntimeException;

class YandexMetrikaRetentionService
{
    private const RETENTION_DAYS = [1, 3, 7, 30];

    private const VISIT_FIELDS = [
        'ym:s:clientID',
        'ym:s:dateTime',
        'ym:s:isNewUser',
    ];

    public function __construct(private YandexMetrikaClient $client)
    {
    }

    /**
     * @throws GuzzleException
     */
    public function weeklyRetention(Carbon $weekStart, Carbon $weekEnd, bool $wait = false, int $waitAttempts = 12, int $waitSeconds = 10): array
    {
        $weekStart = $weekStart->copy()->startOfDay();
        $weekEnd = $weekEnd->copy()->endOfDay();
        $dateFrom = $weekStart->copy()->subDays(max(self::RETENTION_DAYS))->startOfDay();

        $request = $this->loadOrCreateVisitsRequest($dateFrom, $weekEnd);
        $request = $wait
            ? $this->waitForProcessedRequest($request, $waitAttempts, $waitSeconds)
            : $this->assertProcessed($request);

        $visits = $this->downloadVisits($request);
        $firstVisits = [];
        $visitsByClientAndDate = [];

        foreach ($visits as $visit) {
            $clientId = $visit['ym:s:clientID'] ?? null;
            $dateTime = $visit['ym:s:dateTime'] ?? null;

            if (!$clientId || !$dateTime) {
                continue;
            }

            $date = Carbon::parse($dateTime)->toDateString();
            $visitsByClientAndDate[$clientId][$date] = true;

            if (
                (int) ($visit['ym:s:isNewUser'] ?? 0) === 1
                && (!isset($firstVisits[$clientId]) || $date < $firstVisits[$clientId])
            ) {
                $firstVisits[$clientId] = $date;
            }
        }

        $result = [];
        foreach (self::RETENTION_DAYS as $day) {
            $base = 0;
            $returned = 0;

            foreach ($firstVisits as $clientId => $firstVisitDate) {
                $returnDate = Carbon::parse($firstVisitDate)->addDays($day);

                if (!$returnDate->betweenIncluded($weekStart, $weekEnd)) {
                    continue;
                }

                $base++;

                if (isset($visitsByClientAndDate[$clientId][$returnDate->toDateString()])) {
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
            'log_request_id' => (int) ($request['request_id'] ?? 0),
            'retention' => $result,
        ];
    }

    /**
     * @throws GuzzleException
     */
    private function loadOrCreateVisitsRequest(Carbon $dateFrom, Carbon $dateTo): array
    {
        $existing = $this->findVisitsRequest($dateFrom, $dateTo);

        if ($existing !== null) {
            return $existing;
        }

        return $this->client->createLogRequest('visits', $dateFrom, $dateTo, self::VISIT_FIELDS)['log_request'] ?? [];
    }

    /**
     * @throws GuzzleException
     */
    private function findVisitsRequest(Carbon $dateFrom, Carbon $dateTo): ?array
    {
        $fields = self::VISIT_FIELDS;

        foreach ($this->client->logRequests()['requests'] ?? [] as $request) {
            if (
                $this->requestSource($request) === 'visits'
                && ($request['date1'] ?? null) === $dateFrom->toDateString()
                && ($request['date2'] ?? null) === $dateTo->toDateString()
                && ($request['fields'] ?? []) === $fields
            ) {
                return $request;
            }
        }

        return null;
    }

    private function requestSource(array $request): ?string
    {
        $source = $request['source'] ?? null;

        return is_array($source) ? ($source[0] ?? null) : $source;
    }

    /**
     * @throws GuzzleException
     */
    private function waitForProcessedRequest(array $request, int $attempts, int $seconds): array
    {
        $attempts = max(1, $attempts);
        $seconds = max(1, $seconds);
        $requestId = (int) ($request['request_id'] ?? 0);

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $request = $this->client->logRequest($requestId)['log_request'] ?? $request;
            $status = $request['status'] ?? null;

            if ($status === 'processed') {
                return $request;
            }

            if (in_array($status, ['processing_failed', 'canceled'], true)) {
                throw new RuntimeException('Yandex Metrika Logs API request failed: '.$status);
            }

            if ($attempt < $attempts) {
                sleep($seconds);
            }
        }

        throw new RuntimeException('Yandex Metrika Logs API request is not processed yet.');
    }

    private function assertProcessed(array $request): array
    {
        if (($request['status'] ?? null) !== 'processed') {
            throw new RuntimeException('Yandex Metrika Logs API request is not processed yet.');
        }

        return $request;
    }

    /**
     * @throws GuzzleException
     */
    private function downloadVisits(array $request): array
    {
        $requestId = (int) ($request['request_id'] ?? 0);
        $rows = [];

        foreach ($request['parts'] ?? [] as $part) {
            $body = $this->client->downloadLogPart($requestId, (int) ($part['part_number'] ?? 0));
            $rows = array_merge($rows, $this->parseTsv($body));
        }

        return $rows;
    }

    private function parseTsv(string $body): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($body));
        if (!$lines || $lines === ['']) {
            return [];
        }

        $header = str_getcsv(array_shift($lines), "\t");
        $rows = [];

        foreach ($lines as $line) {
            if ($line === '') {
                continue;
            }

            $values = str_getcsv($line, "\t");
            $rows[] = array_combine($header, $values) ?: [];
        }

        return $rows;
    }
}
