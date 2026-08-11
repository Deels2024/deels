<?php

declare(strict_types=1);

namespace App\Services\YandexMetrika;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use InvalidArgumentException;

class YandexMetrikaClient
{
    private Client $client;

    private ?string $accessToken;

    private ?string $counterId;

    public function __construct()
    {
        $config = config('services.yandex_metrika');

        $this->accessToken = Arr::get($config, 'access_token');
        $this->counterId = Arr::get($config, 'counter_id');

        $this->client = new Client([
            'base_uri' => rtrim((string) Arr::get($config, 'base_url', 'https://api-metrika.yandex.net'), '/').'/',
            'timeout' => (float) Arr::get($config, 'timeout', 15),
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function counters(): array
    {
        return $this->get('management/v1/counters');
    }

    /**
     * @throws GuzzleException
     */
    public function report(array $params): array
    {
        return $this->get('stat/v1/data', $this->withCounterId($params));
    }

    /**
     * @throws GuzzleException
     */
    public function logRequests(): array
    {
        return $this->get(sprintf('management/v1/counter/%s/logrequests', $this->counterId()));
    }

    /**
     * @throws GuzzleException
     */
    public function createLogRequest(string $source, Carbon|string $dateFrom, Carbon|string $dateTo, array $fields): array
    {
        return $this->post(sprintf('management/v1/counter/%s/logrequests', $this->counterId()), [
            'source' => $source,
            'date1' => $this->dateValue($dateFrom),
            'date2' => $this->dateValue($dateTo),
            'fields' => implode(',', $fields),
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function logRequest(int $requestId): array
    {
        return $this->get(sprintf('management/v1/counter/%s/logrequest/%d', $this->counterId(), $requestId));
    }

    /**
     * @throws GuzzleException
     */
    public function downloadLogPart(int $requestId, int $partNumber): string
    {
        $this->assertAccessToken();

        $response = $this->client->get(sprintf(
            'management/v1/counter/%s/logrequest/%d/part/%d/download',
            $this->counterId(),
            $requestId,
            $partNumber
        ), [
            'headers' => [
                'Authorization' => 'OAuth '.$this->accessToken,
                'Accept' => 'text/tab-separated-values',
            ],
        ]);

        return (string) $response->getBody();
    }

    /**
     * @throws GuzzleException
     */
    private function get(string $path, array $query = []): array
    {
        $this->assertAccessToken();

        $response = $this->client->get(ltrim($path, '/'), [
            'headers' => [
                'Authorization' => 'OAuth '.$this->accessToken,
                'Accept' => 'application/json',
            ],
            'query' => $query,
        ]);

        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @throws GuzzleException
     */
    private function post(string $path, array $query = []): array
    {
        $this->assertAccessToken();

        $response = $this->client->post(ltrim($path, '/'), [
            'headers' => [
                'Authorization' => 'OAuth '.$this->accessToken,
                'Accept' => 'application/json',
            ],
            'query' => $query,
        ]);

        return json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
    }

    private function withCounterId(array $params): array
    {
        return array_merge(['id' => $this->counterId()], $params);
    }

    private function counterId(): string
    {
        if (!$this->counterId) {
            throw new InvalidArgumentException('YANDEX_METRIKA_COUNTER_ID is not configured.');
        }

        return $this->counterId;
    }

    private function dateValue(Carbon|string $date): string
    {
        return $date instanceof Carbon ? $date->toDateString() : $date;
    }

    private function assertAccessToken(): void
    {
        if (!$this->accessToken) {
            throw new InvalidArgumentException('YANDEX_METRIKA_ACCESS_TOKEN is not configured.');
        }
    }
}
