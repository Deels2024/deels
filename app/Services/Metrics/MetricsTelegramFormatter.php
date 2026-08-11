<?php

declare(strict_types=1);

namespace App\Services\Metrics;

use Illuminate\Support\Carbon;

class MetricsTelegramFormatter
{
    public function daily(array $report): string
    {
        $date = Carbon::parse($report['period']['date_to'])->format('d.m.y');
        [$metrics, $site, $app] = $this->metricsBySection($report);

        return implode("\n", array_filter([
            'Дата: '.$date,
            '',
            'DAU: '.$this->sectionNumber($metrics['dau'] ?? null, $site['dau'] ?? null, $app['dau'] ?? null).' пользователей',
            'Коэф. липкости: '.$this->sectionPercent($metrics['stickiness'] ?? null, $site['stickiness'] ?? null, $app['stickiness'] ?? null),
            'Время на платформе: '.$this->sectionMinutes($metrics['time_on_platform_minutes'] ?? null, $site['time_on_platform_minutes'] ?? null, $app['time_on_platform_minutes'] ?? null),
            'Сред. взаимодействий: '.$this->sectionNumber($metrics['avg_interactions'] ?? null, $site['avg_interactions'] ?? null, $app['avg_interactions'] ?? null),
            'Число смс за сутки: '.$this->sectionNumber($metrics['messages_count'] ?? null, $site['messages_count'] ?? null, $app['messages_count'] ?? null),
            '% общающихся: '.$this->sectionPercent($metrics['chatting_users_percent'] ?? null, $site['chatting_users_percent'] ?? null, $app['chatting_users_percent'] ?? null),
            'Объем внутр. экон.: '.$this->sectionRub($metrics['internal_economy'] ?? null, $site['internal_economy'] ?? null, $app['internal_economy'] ?? null),
            'Расход: '.$this->sectionRub($metrics['expense'] ?? null, $site['expense'] ?? null, $app['expense'] ?? null),
            'Чистая прибыль: '.$this->sectionRub($metrics['net_profit'] ?? null, $site['net_profit'] ?? null, $app['net_profit'] ?? null),
        ], static fn ($line) => $line !== null));
    }

    public function weekly(array $report): string
    {
        $date = Carbon::parse($report['period']['date_to'])->format('d.m.y');
        $dateFrom = Carbon::parse($report['period']['date_from'])->format('d.m.');
        $dateTo = Carbon::parse($report['period']['date_to'])->format('d.m.y');
        [$metrics, $site, $app] = $this->metricsBySection($report);

        $lines = [
            'Дата: '.$date,
            '',
            'DAU: '.$this->sectionNumber($metrics['dau'] ?? null, $site['dau'] ?? null, $app['dau'] ?? null).' пользователей',
            'Коэф. липкости: '.$this->sectionPercent($metrics['stickiness'] ?? null, $site['stickiness'] ?? null, $app['stickiness'] ?? null),
            'Время на платформе: '.$this->sectionMinutes($metrics['time_on_platform_minutes'] ?? null, $site['time_on_platform_minutes'] ?? null, $app['time_on_platform_minutes'] ?? null),
            'Сред. взаимодействий: '.$this->sectionNumber($metrics['avg_interactions'] ?? null, $site['avg_interactions'] ?? null, $app['avg_interactions'] ?? null),
            'Число смс за сутки: '.$this->sectionNumber($metrics['messages_count'] ?? null, $site['messages_count'] ?? null, $app['messages_count'] ?? null),
            '% общающихся: '.$this->sectionPercent($metrics['chatting_users_percent'] ?? null, $site['chatting_users_percent'] ?? null, $app['chatting_users_percent'] ?? null),
            'Объем внутр. экон.: '.$this->sectionRub($metrics['internal_economy'] ?? null, $site['internal_economy'] ?? null, $app['internal_economy'] ?? null),
            'Расход: '.$this->sectionRub($metrics['expense'] ?? null, $site['expense'] ?? null, $app['expense'] ?? null),
            'Чистая прибыль: '.$this->sectionRub($metrics['net_profit'] ?? null, $site['net_profit'] ?? null, $app['net_profit'] ?? null),
            '',
            'Неделя: '.$dateFrom.'-'.$dateTo,
            '',
            'Когортное удерж.: '.$this->retention($report['retention'] ?? null),
            'Когортное удерж. сайт: '.$this->retention($report['site_retention'] ?? null),
            'Когортное удерж. прил.: '.$this->retention($report['app_retention'] ?? null),
            'Коэф. виральности: '.$this->sectionNumber($metrics['virality_rate'] ?? null, $site['virality_rate'] ?? null, $app['virality_rate'] ?? null),
            'Коэф. участия: '.$this->sectionNumber($metrics['participation_rate'] ?? null, $site['participation_rate'] ?? null, $app['participation_rate'] ?? null),
            '% актив. создателей: '.$this->sectionPercent($metrics['active_creators_percent'] ?? null, $site['active_creators_percent'] ?? null, $app['active_creators_percent'] ?? null),
            '% регул. создателей: '.$this->sectionPercent($metrics['regular_creators_percent'] ?? null, $site['regular_creators_percent'] ?? null, $app['regular_creators_percent'] ?? null),
            'Валовый доход: '.$this->sectionRub($metrics['gross_revenue'] ?? null, $site['gross_revenue'] ?? null, $app['gross_revenue'] ?? null),
            '% доходности: '.$this->sectionPercent($metrics['profitability_percent'] ?? null, $site['profitability_percent'] ?? null, $app['profitability_percent'] ?? null),
            'Прибыльность польз.: '.$this->sectionRub($metrics['profit_per_user'] ?? null, $site['profit_per_user'] ?? null, $app['profit_per_user'] ?? null),
        ];

        return implode("\n", $lines);
    }

    private function metricsBySection(array $report): array
    {
        $metrics = $report['metrics'] ?? [];
        $database = array_merge($report['database'] ?? [], $report['database_daily'] ?? [], $report['database_weekly'] ?? []);
        $site = array_merge(array_fill_keys(array_keys($metrics), null), $report['site'] ?? [], [
            'messages_count' => $database['site_messages_count'] ?? null,
        ]);
        $app = array_merge(array_fill_keys(array_keys($metrics), null), $report['appmetrica'] ?? [], [
            'messages_count' => $database['app_messages_count'] ?? null,
        ]);

        return [$metrics, $site, $app];
    }

    private function retention(?array $report): string
    {
        if (!empty($report['retention'])) {
            $values = [];
            foreach ([1, 3, 7, 30] as $day) {
                $rate = $report['retention'][$day]['rate'] ?? null;
                $values[] = $day.'д '.$this->percent($rate);
            }

            return implode(', ', $values);
        }

        return 'н/д';
    }

    private function sectionNumber(mixed $total, mixed $site, mixed $app): string
    {
        return $this->numberOrNa($total).' ('.$this->numberOrNa($site).' / '.$this->numberOrNa($app).')';
    }

    private function sectionPercent(mixed $total, mixed $site, mixed $app): string
    {
        return $this->percent($total).' ('.$this->percent($site).' / '.$this->percent($app).')';
    }

    private function sectionMinutes(mixed $total, mixed $site, mixed $app): string
    {
        return $this->minutes($total).' ('.$this->minutes($site).' / '.$this->minutes($app).')';
    }

    private function sectionRub(mixed $total, mixed $site, mixed $app): string
    {
        return $this->rubOrNa($total).' ('.$this->rubOrNa($site).' / '.$this->rubOrNa($app).')';
    }

    private function percent(mixed $value): string
    {
        return $value === null ? 'н/д' : $this->number($value).'%';
    }

    private function minutes(mixed $value): string
    {
        return $value === null ? 'н/д' : $this->number($value).' мин.';
    }

    private function rub(mixed $value): string
    {
        return $this->number((float) $value).' руб.';
    }

    private function rubOrNa(mixed $value): string
    {
        return $value === null ? 'н/д' : $this->rub($value);
    }

    private function numberOrNa(mixed $value): string
    {
        return $value === null ? 'н/д' : $this->number($value);
    }

    private function number(mixed $value): string
    {
        if (!is_numeric($value)) {
            return (string) $value;
        }

        $number = (float) $value;
        $precision = floor($number) === $number ? 0 : 2;

        return number_format($number, $precision, '.', ' ');
    }
}
