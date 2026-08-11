<?php

declare(strict_types=1);

namespace App\Services;

use App\Helpers\SMSCHelper;
use App\Models\ServiceBalanceStatistic;
use GuzzleHttp\Client;
use Throwable;

class ServiceBalanceStatisticsService
{
    public function latest(): array
    {
        $statistic = ServiceBalanceStatistic::query()
            ->latest('checked_at')
            ->first();

        if (!$statistic) {
            return $this->emptyData();
        }

        return [
            'ucaller_balance' => $statistic->ucaller_balance ?? 0,
            'sms_balance' => $statistic->sms_balance ?? 0,
            'proxy_balance' => $statistic->proxy_balance ?? 0,
            'proxies' => $statistic->proxies ?? [],
            'checked_at' => $statistic->checked_at,
            'errors' => $statistic->errors ?? [],
        ];
    }

    public function collect(): ServiceBalanceStatistic
    {
        $data = $this->emptyData();
        $errors = [];

        try {
            $endpoint = 'https://api.ucaller.ru/v1.0/getBalance?key=' . env('UCALLER_SECRET') . '&service_id=' . env('UCALLER_ID');
            $content = $this->getJson($endpoint);
            $data['ucaller_balance'] = $content['rub_balance'] ?? 0;
        } catch (Throwable $e) {
            $errors['ucaller'] = $e->getMessage();
        }

        try {
            $smscHelper = new SMSCHelper();
            $data['sms_balance'] = $smscHelper->get_balance();
        } catch (Throwable $e) {
            $errors['smsc'] = $e->getMessage();
        }

        try {
            $content = $this->getJsonWithProxyRetry('https://proxy6.net/api/60426d78cc-df5fdd322a-0c05b2a7ba/getproxy');
            $data['proxy_balance'] = $content['balance'] ?? 0;
            $data['proxies'] = $content['list'] ?? [];
        } catch (Throwable $e) {
            $errors['proxy6'] = $e->getMessage();
        }

        return ServiceBalanceStatistic::query()->create([
            'ucaller_balance' => $data['ucaller_balance'],
            'sms_balance' => $data['sms_balance'],
            'proxy_balance' => $data['proxy_balance'],
            'proxies' => $data['proxies'],
            'errors' => $errors,
            'checked_at' => now(),
        ]);
    }

    private function emptyData(): array
    {
        return [
            'ucaller_balance' => 0,
            'sms_balance' => 0,
            'proxy_balance' => 0,
            'proxies' => [],
            'checked_at' => null,
            'errors' => [],
        ];
    }

    private function getJson(string $endpoint): array
    {
        $client = new Client();
        $response = $client->request('GET', $endpoint, []);
        $content = $response->getBody()->getContents();

        if (is_array($content)) {
            return $content;
        }

        return json_decode($content, true) ?: [];
    }

    private function getJsonWithProxyRetry(string $endpoint): array
    {
        $proxyAddress = env('PROXY_ADDRESS');
        $proxyPort = env('PROXY_PORT');

        if (!empty($proxyAddress) && !empty($proxyPort)) {
            $content = $this->getJsonViaProxy($endpoint, $proxyAddress, $proxyPort);

            if (!empty($content)) {
                return $content;
            }
        }

        return $this->getJson($endpoint);
    }

    private function getJsonViaProxy(string $endpoint, string $proxyAddress, string $proxyPort): array
    {

        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        curl_setopt($ch, CURLOPT_PROXY, $proxyAddress);
        curl_setopt($ch, CURLOPT_PROXYPORT, (int) $proxyPort);

        $proxyLogin = env('PROXY_LOGIN');
        $proxyPassword = env('PROXY_PASSWORD');
        if (!empty($proxyLogin) || !empty($proxyPassword)) {
            curl_setopt($ch, CURLOPT_PROXYUSERPWD, $proxyLogin . ':' . $proxyPassword);
        }

        switch (strtolower((string) env('PROXY_SCHEME'))) {
            case 'socks5':
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
                break;
            case 'socks4':
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS4);
                break;
            case 'http':
            case 'https':
            default:
                curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
                break;
        }

        $result = curl_exec($ch);
        curl_close($ch);

        if ($result === false) {
            return [];
        }

        return json_decode($result, true) ?: [];
    }
}
