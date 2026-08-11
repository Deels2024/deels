<?php

declare(strict_types=1);

namespace App\Services\AppMetrica;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use InvalidArgumentException;
use RuntimeException;

class AppMetricaClient
{
    private Client $statClient;

    private Client $managementClient;

    private Client $oauthClient;

    private ?string $clientId;

    private ?string $clientSecret;

    private ?string $redirectUri;

    private ?string $accessToken;

    private ?string $refreshToken;

    private ?string $applicationId;

    private string $oauthBaseUrl;

    public function __construct()
    {
        $config = config('services.appmetrica');

        $this->clientId = Arr::get($config, 'client_id');
        $this->clientSecret = Arr::get($config, 'client_secret');
        $this->redirectUri = Arr::get($config, 'redirect_uri');
        $this->accessToken = Arr::get($config, 'access_token');
        $this->refreshToken = Arr::get($config, 'refresh_token');
        $this->applicationId = Arr::get($config, 'application_id');
        $this->oauthBaseUrl = $this->baseUrl($config, 'oauth_base_url', 'https://oauth.yandex.ru');

        $timeout = (float) Arr::get($config, 'timeout', 15);

        $this->statClient = new Client([
            'base_uri' => $this->baseUrl($config, 'stat_base_url', 'https://api.appmetrica.yandex.com').'/',
            'timeout' => $timeout,
        ]);

        $this->managementClient = new Client([
            'base_uri' => $this->baseUrl($config, 'management_base_url', 'https://api.appmetrica.yandex.com').'/',
            'timeout' => $timeout,
        ]);

        $this->oauthClient = new Client([
            'base_uri' => $this->oauthBaseUrl.'/',
            'timeout' => $timeout,
        ]);
    }

    public function getAuthorizationUrl(array $scopes = [], ?string $state = null): string
    {
        $this->assertOAuthConfig();

        $query = array_filter([
            'response_type' => 'code',
            'client_id' => $this->clientId,
            'redirect_uri' => $this->redirectUri,
            'scope' => $scopes ? implode(',', $scopes) : null,
            'state' => $state,
        ], static fn ($value) => $value !== null && $value !== '');

        return $this->oauthBaseUrl.'/authorize?'.http_build_query($query);
    }

    /**
     * @throws GuzzleException
     */
    public function exchangeCodeForToken(string $code): array
    {
        $this->assertOAuthConfig();

        return $this->oauthRequest('token', [
            'grant_type' => 'authorization_code',
            'code' => $code,
            'redirect_uri' => $this->redirectUri,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function refreshAccessToken(?string $refreshToken = null): array
    {
        $this->assertOAuthConfig();

        $token = $refreshToken ?: $this->refreshToken;
        if (!$token) {
            throw new InvalidArgumentException('AppMetrica refresh token is not configured.');
        }

        return $this->oauthRequest('token', [
            'grant_type' => 'refresh_token',
            'refresh_token' => $token,
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function applications(): array
    {
        return $this->managementGet('management/v1/applications');
    }

    /**
     * @throws GuzzleException
     */
    public function report(array $params): array
    {
        return $this->get('stat/v1/data', $this->withApplicationId($params));
    }

    /**
     * @throws GuzzleException
     */
    public function reportByTime(array $params): array
    {
        return $this->get('stat/v1/data/bytime', $this->withApplicationId($params));
    }

    /**
     * @throws GuzzleException
     */
    public function dailyReport(Carbon $date, string $metrics, ?string $dimensions = null, array $params = []): array
    {
        $dateValue = $date->toDateString();

        return $this->report(array_filter(array_merge($params, [
            'date1' => $dateValue,
            'date2' => $dateValue,
            'metrics' => $metrics,
            'dimensions' => $dimensions,
        ]), static fn ($value) => $value !== null && $value !== ''));
    }

    /**
     * @throws GuzzleException
     */
    public function get(string $path, array $query = []): array
    {
        return $this->authorizedGet($this->statClient, $path, $query);
    }

    /**
     * @throws GuzzleException
     */
    public function managementGet(string $path, array $query = []): array
    {
        $this->assertAccessToken();

        return $this->authorizedGet($this->managementClient, $path, $query);
    }

    /**
     * @throws GuzzleException
     */
    public function logsApiStatus(): array
    {
        return $this->managementGet('management/v1/logsapi/status');
    }

    /**
     * Logs API returns 202 while export is being prepared and 200 with data when ready.
     *
     * @throws GuzzleException
     */
    public function logsExport(string $resource, array $query, string $format = 'json'): array
    {
        $this->assertAccessToken();

        $response = $this->managementClient->get(
            sprintf('logs/v1/export/%s.%s', trim($resource, '/'), $format),
            [
                RequestOptions::HEADERS => [
                    'Authorization' => 'OAuth '.$this->accessToken,
                    'Accept' => $format === 'json' ? 'application/json' : 'text/csv',
                ],
                RequestOptions::QUERY => $this->withApplicationIdForLogs($query),
                RequestOptions::HTTP_ERRORS => false,
            ]
        );

        $body = (string) $response->getBody();
        $status = $response->getStatusCode();

        return [
            'status' => $status,
            'body' => $body,
            'json' => $status === 200 && $format === 'json' ? $this->decodeJsonLines($body) : null,
        ];
    }

    /**
     * @throws GuzzleException
     */
    private function authorizedGet(Client $client, string $path, array $query = []): array
    {
        $this->assertAccessToken();

        $response = $client->get(ltrim($path, '/'), [
            'headers' => [
                'Authorization' => 'OAuth '.$this->accessToken,
                'Accept' => 'application/json',
            ],
            'query' => $query,
        ]);

        return $this->decodeJson((string) $response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    private function oauthRequest(string $path, array $formParams): array
    {
        $response = $this->oauthClient->post(ltrim($path, '/'), [
            'headers' => [
                'Authorization' => 'Basic '.base64_encode($this->clientId.':'.$this->clientSecret),
                'Accept' => 'application/json',
            ],
            'form_params' => $formParams,
        ]);

        return $this->decodeJson((string) $response->getBody());
    }

    private function withApplicationId(array $params): array
    {
        if (!isset($params['id']) && !isset($params['ids'])) {
            if (!$this->applicationId) {
                throw new InvalidArgumentException('AppMetrica application id is not configured.');
            }

            $params['id'] = $this->applicationId;
        }

        return $params;
    }

    private function withApplicationIdForLogs(array $params): array
    {
        if (!isset($params['application_id'])) {
            if (!$this->applicationId) {
                throw new InvalidArgumentException('AppMetrica application id is not configured.');
            }

            $params['application_id'] = $this->applicationId;
        }

        return $params;
    }

    private function assertOAuthConfig(): void
    {
        if (!$this->clientId || !$this->clientSecret) {
            throw new InvalidArgumentException('AppMetrica client id or client secret is not configured.');
        }
    }

    private function assertAccessToken(): void
    {
        if (!$this->accessToken) {
            throw new InvalidArgumentException('AppMetrica access token is not configured.');
        }
    }

    private function decodeJson(string $body): array
    {
        $data = json_decode($body, true);

        if (!is_array($data)) {
            throw new RuntimeException('AppMetrica returned invalid JSON response.');
        }

        return $data;
    }

    private function decodeJsonLines(string $body): array
    {
        $trimmed = trim($body);
        if ($trimmed === '') {
            return [];
        }

        $decoded = json_decode($trimmed, true);
        if (is_array($decoded)) {
            if (isset($decoded['data']) && is_array($decoded['data'])) {
                return $decoded['data'];
            }

            return array_is_list($decoded) ? $decoded : [$decoded];
        }

        $rows = [];
        foreach (preg_split('/\R/', $trimmed) ?: [] as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }

            $row = json_decode($line, true);
            if (is_array($row)) {
                $rows[] = $row;
            }
        }

        return $rows;
    }

    private function baseUrl(array $config, string $key, string $default): string
    {
        $value = (string) Arr::get($config, $key, $default);

        return rtrim($value ?: $default, '/');
    }
}
