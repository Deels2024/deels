<?php

declare(strict_types=1);

namespace App\Console\Commands\YandexMetrika;

use App\Services\YandexMetrika\YandexMetrikaClient;
use App\Services\YandexMetrika\YandexMetrikaMetricsService;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class CheckYandexMetrika extends Command
{
    protected $signature = 'yandex-metrika:check {--date= : Date to check in Y-m-d format, yesterday by default}';

    protected $description = 'Check Yandex Metrika OAuth credentials, counter access, and a basic Reporting API request.';

    public function handle(YandexMetrikaClient $client, YandexMetrikaMetricsService $metrics): int
    {
        $counterId = config('services.yandex_metrika.counter_id');
        $date = $this->option('date')
            ? Carbon::createFromFormat('Y-m-d', (string) $this->option('date'))->startOfDay()
            : Carbon::yesterday()->startOfDay();

        $this->info('Checking Yandex Metrika connection...');
        $this->line('Counter ID: '.($counterId ?: '<not configured>'));
        $this->line('Date: '.$date->toDateString());

        try {
            $counters = $client->counters();
            $items = $counters['counters'] ?? [];

            $this->info('Management API: OK, counters available: '.count($items));

            foreach ($items as $item) {
                $this->line(sprintf(
                    '- %s (id: %s, site: %s, permission: %s)',
                    $item['name'] ?? '<unnamed>',
                    $item['id'] ?? '<unknown>',
                    $item['site'] ?? '<unknown>',
                    $item['permission'] ?? '<unknown>'
                ));
            }

            $summary = $metrics->dailySummary($date);

            $this->info('Reporting API: OK');
            $this->table(
                ['date', 'users', 'wau', 'stickiness', 'visits', 'time/user min'],
                [[
                    $summary['date'],
                    $summary['dau'],
                    $summary['wau'],
                    $summary['stickiness'] === null ? 'n/a' : $summary['stickiness'].'%',
                    $summary['sessions'],
                    $summary['time_on_platform_minutes'] === null ? 'n/a' : $summary['time_on_platform_minutes'],
                ]]
            );

            return self::SUCCESS;
        } catch (RequestException $e) {
            $this->error('Yandex Metrika request failed: '.$e->getMessage());

            $response = $e->getResponse();
            if ($response) {
                $this->line((string) $response->getBody());
            }

            return self::FAILURE;
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }
    }
}
