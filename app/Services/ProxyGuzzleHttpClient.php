<?php

namespace App\Services;

use GuzzleHttp\Client;
use Telegram\Bot\HttpClients\GuzzleHttpClient;

class ProxyGuzzleHttpClient extends GuzzleHttpClient
{
    public function __construct()
    {
        parent::__construct(new Client($this->buildOptions()));
    }

    private function buildOptions(): array
    {
        $options = [
            'http_errors' => false,
            'verify' => false,
            'connect_timeout' => 10,
            'timeout' => 20,
        ];

        $scheme = (string) env('PROXY_SCHEME', 'http');
        $address = (string) env('PROXY_ADDRESS');
        $port = (string) env('PROXY_PORT');
        $login = (string) env('PROXY_LOGIN');
        $password = (string) env('PROXY_PASSWORD');

        if ($address !== '' && $port !== '') {
            $auth = '';
            if ($login !== '' || $password !== '') {
                $auth = rawurlencode($login) . ':' . rawurlencode($password) . '@';
            }

            $proxyScheme = $this->normalizeProxyScheme($scheme);
            $proxy = sprintf('%s://%s%s:%s', $proxyScheme, $auth, $address, $port);
            $options['proxy'] = [
                'http' => $proxy,
                'https' => $proxy,
            ];
        }

        return $options;
    }

    private function normalizeProxyScheme(string $scheme): string
    {
        $scheme = strtolower(trim($scheme));

        return match ($scheme) {
            'socks5', 'socks5h' => 'socks5h',
            'socks4' => 'socks4',
            // For curl/guzzle HTTP CONNECT proxies, use http scheme for both http/https targets.
            default => 'http',
        };
    }
}
