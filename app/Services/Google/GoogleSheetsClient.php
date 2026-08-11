<?php

declare(strict_types=1);

namespace App\Services\Google;

use Google\Auth\Credentials\ServiceAccountCredentials;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use InvalidArgumentException;
use RuntimeException;

class GoogleSheetsClient
{
    private const SCOPE = 'https://www.googleapis.com/auth/spreadsheets';

    private Client $client;

    private ServiceAccountCredentials $credentials;

    private string $spreadsheetId;

    public function __construct()
    {
        $config = config('services.google_sheets');
        $credentialsPath = (string) Arr::get($config, 'credentials_path');
        $this->spreadsheetId = trim((string) Arr::get($config, 'metrics_spreadsheet_id'), '"');

        if ($credentialsPath === '' || !is_file($credentialsPath)) {
            throw new InvalidArgumentException('Google Sheets credentials file is not configured or does not exist.');
        }

        if ($this->spreadsheetId === '') {
            throw new InvalidArgumentException('Google metrics spreadsheet id is not configured.');
        }

        $json = json_decode((string) file_get_contents($credentialsPath), true);
        if (!is_array($json)) {
            throw new InvalidArgumentException('Google Sheets credentials file contains invalid JSON.');
        }

        $this->credentials = new ServiceAccountCredentials(self::SCOPE, $json);
        $this->client = new Client([
            'base_uri' => 'https://sheets.googleapis.com/',
            'timeout' => (float) Arr::get($config, 'timeout', 20),
        ]);
    }

    /**
     * @throws GuzzleException
     */
    public function getValues(string $range): array
    {
        $response = $this->client->get($this->path('values/'.rawurlencode($range)), [
            'headers' => $this->headers(),
        ]);

        return $this->decode((string) $response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function updateValues(string $range, array $values): array
    {
        $response = $this->client->put($this->path('values/'.rawurlencode($range)), [
            'headers' => $this->headers(),
            'query' => ['valueInputOption' => 'USER_ENTERED'],
            'json' => [
                'range' => $range,
                'majorDimension' => 'ROWS',
                'values' => $values,
            ],
        ]);

        return $this->decode((string) $response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function spreadsheet(): array
    {
        $response = $this->client->get('v4/spreadsheets/'.$this->spreadsheetId, [
            'headers' => $this->headers(),
        ]);

        return $this->decode((string) $response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function insertColumns(string $sheetName, int $column, int $count = 1): array
    {
        $sheetId = $this->sheetId($sheetName);

        $response = $this->client->post('v4/spreadsheets/'.$this->spreadsheetId.':batchUpdate', [
            'headers' => $this->headers(),
            'json' => [
                'requests' => [[
                    'insertDimension' => [
                        'range' => [
                            'sheetId' => $sheetId,
                            'dimension' => 'COLUMNS',
                            'startIndex' => $column - 1,
                            'endIndex' => $column - 1 + $count,
                        ],
                        'inheritFromBefore' => $column > 1,
                    ],
                ]],
            ],
        ]);

        return $this->decode((string) $response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    public function setColumnWidth(string $sheetName, int $startColumn, int $endColumn, int $pixelSize): array
    {
        $sheetId = $this->sheetId($sheetName);

        $response = $this->client->post('v4/spreadsheets/'.$this->spreadsheetId.':batchUpdate', [
            'headers' => $this->headers(),
            'json' => [
                'requests' => [[
                    'updateDimensionProperties' => [
                        'range' => [
                            'sheetId' => $sheetId,
                            'dimension' => 'COLUMNS',
                            'startIndex' => $startColumn - 1,
                            'endIndex' => $endColumn,
                        ],
                        'properties' => [
                            'pixelSize' => $pixelSize,
                        ],
                        'fields' => 'pixelSize',
                    ],
                ]],
            ],
        ]);

        return $this->decode((string) $response->getBody());
    }

    /**
     * @throws GuzzleException
     */
    private function sheetId(string $sheetName): int
    {
        foreach ($this->spreadsheet()['sheets'] ?? [] as $sheet) {
            if (($sheet['properties']['title'] ?? null) === $sheetName) {
                return (int) $sheet['properties']['sheetId'];
            }
        }

        throw new InvalidArgumentException('Google sheet not found: '.$sheetName);
    }

    private function path(string $suffix): string
    {
        return 'v4/spreadsheets/'.$this->spreadsheetId.'/'.$suffix;
    }

    private function headers(): array
    {
        $token = $this->credentials->fetchAuthToken()['access_token'] ?? null;
        if (!$token) {
            throw new RuntimeException('Unable to fetch Google Sheets access token.');
        }

        return [
            'Authorization' => 'Bearer '.$token,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
        ];
    }

    private function decode(string $body): array
    {
        $data = json_decode($body, true);
        if (!is_array($data)) {
            throw new RuntimeException('Google Sheets returned invalid JSON response.');
        }

        return $data;
    }
}
