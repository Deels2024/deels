<?php

declare(strict_types=1);

namespace App\Console\Commands\System;

use App\Helpers\AppHelper;
use Carbon\Carbon;
use Illuminate\Console\Command;

class Check6proxy extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = '6proxy:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check 6proxy';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $telegram = new AppHelper();
        try {
            $endpoint = 'https://proxy6.net/api/60426d78cc-df5fdd322a-0c05b2a7ba/getproxy';
            $content = $this->getJsonWithProxyRetry($endpoint);
            $proxy_balance = intval($content['balance']);
            $proxies = $content['list'] ?? [];

            if($proxy_balance <= 200) {
                $telegram->telegram_group_message('⚠️ Внимание! 6proxy Баланс <= 200 руб. ('.$proxy_balance.')');
            }
            $now = Carbon::now()->addDays(7);
            foreach($proxies as $key => $proxy) {
                $proxy_ends = \Carbon\Carbon::parse($proxy['date_end']);
                $diff = $now->diffInDays($proxy_ends);
                if(!$proxy_ends->isPast()) {
                    if($diff <= 7) {
                        $telegram->telegram_group_message('⚠️ Прокси '.$proxy['ip'].' кончится через '.$diff.' '.trans_choice('numbers.days', $diff));
                    }
                } else {
                    $telegram->telegram_group_message('⚠️ Прокси '.$proxy['ip'].' просрочен!');
                }

            }
            $telegram->telegram_message('6proxy check is OK');
        } catch (\Throwable $e) {
            $telegram->telegram_message('6proxy check error: '.$e->getMessage());
        }



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

    private function getJson(string $endpoint): array
    {
        $client = new \GuzzleHttp\Client();
        $response = $client->request('GET', $endpoint, []);
        $content = $response->getBody()->getContents();

        if (is_array($content)) {
            return $content;
        }

        return json_decode($content, true) ?: [];
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
