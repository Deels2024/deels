<?php

declare(strict_types=1);

namespace App\Services\Metrics;

use App\Services\Google\GoogleSheetsClient;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;

class MetricsGoogleSheetsService
{
    private const HEADER_ROW = 2;

    private const SECTION_COLUMN = 2;

    private const METRIC_COLUMN = 3;

    private const FIRST_DATE_COLUMN = 4;

    private const DATE_COLUMN_WIDTH = 92;

    private string $sheetName;

    public function __construct(private GoogleSheetsClient $client)
    {
        $this->sheetName = (string) config('services.google_sheets.metrics_sheet_name', 'Статистика');
    }

    public function dailyRows(array $report): array
    {
        return array_merge(
            $this->dailyTotalRows($report),
            $this->dailySiteRows($report),
            $this->dailyApplicationRows($report)
        );
    }

    public function templateRows(): array
    {
        $daily = [
            ['Общее', 'DAU (за день)'],
            ['', 'Коэф. липкости (%, за день)'],
            ['', 'Время на платформе (мин., за день)'],
            ['', 'Сред. число взаимодействий (за день)'],
            ['', 'Число смс за сутки (за день)'],
            ['', '% общающихся пользователей (%, за день)'],
            ['', 'Объем внутр. экономики (руб., за день)'],
            ['', 'Расход (руб., за день)'],
            ['', 'Чистая прибыль (руб., за день)'],
            ['', 'Когортное удержание (%, за неделю, 1/3/7/30 д.)'],
            ['', 'Коэф. виральности (за неделю)'],
            ['', 'Коэф. участия (за неделю)'],
            ['', '% активных создателей (%, за неделю)'],
            ['', '% регулярных создателей (%, за неделю)'],
            ['', 'Валовый доход (руб., за неделю)'],
            ['', '% доходности (%, за неделю)'],
            ['', 'Прибыльность 1 пользователя (руб., за неделю)'],
        ];

        $site = [
            ['Сайт', 'DAU (за день)'],
            ['', 'Коэф. липкости (%, за день)'],
            ['', 'Время на платформе (мин., за день)'],
            ['', 'Число смс за сутки (за день)'],
            ['', 'Когортное удержание (%, за неделю, 1/3/7/30 д.)'],
        ];

        $app = [
            ['Приложение', 'DAU (за день)'],
            ['', 'Коэф. липкости (%, за день)'],
            ['', 'Время на платформе (мин., за день)'],
            ['', 'Число смс за сутки (за день)'],
            ['', 'Когортное удержание (%, за неделю, 1/3/7/30 д.)'],
        ];

        return array_merge($daily, $site, $app);
    }

    public function weeklyRows(array $report): array
    {
        return array_merge(
            $this->weeklyTotalRows($report),
            $this->weeklySiteRows($report),
            $this->weeklyApplicationRows($report)
        );
    }

    private function dailyTotalRows(array $report): array
    {
        return $this->dailyRowsForSection('Общее', $this->totalMetrics($report));
    }

    private function dailySiteRows(array $report): array
    {
        return $this->analyticsRowsForSection('Сайт', $this->siteMetrics($report));
    }

    private function dailyApplicationRows(array $report): array
    {
        return $this->analyticsRowsForSection('Приложение', $this->applicationMetrics($report));
    }

    private function weeklyTotalRows(array $report): array
    {
        return $this->weeklyRowsForSection('Общее', $this->totalMetrics($report), $this->retentionValue($report));
    }

    private function weeklySiteRows(array $report): array
    {
        return $this->analyticsRowsForSection(
            'Сайт',
            $this->siteMetrics($report),
            $this->retentionReportValue($report['site_retention'] ?? null)
        );
    }

    private function weeklyApplicationRows(array $report): array
    {
        return $this->analyticsRowsForSection(
            'Приложение',
            $this->applicationMetrics($report),
            $this->retentionReportValue($report['app_retention'] ?? null)
        );
    }

    private function dailyRowsForSection(string $section, array $metrics): array
    {
        return [
            [$section, 'DAU (за день)', $metrics['dau']],
            ['', 'Коэф. липкости (%, за день)', $metrics['stickiness']],
            ['', 'Время на платформе (мин., за день)', $metrics['time_on_platform_minutes']],
            ['', 'Сред. число взаимодействий (за день)', $metrics['avg_interactions']],
            ['', 'Число смс за сутки (за день)', $metrics['messages_count']],
            ['', '% общающихся пользователей (%, за день)', $metrics['chatting_users_percent']],
            ['', 'Объем внутр. экономики (руб., за день)', $metrics['internal_economy']],
            ['', 'Расход (руб., за день)', $metrics['expense']],
            ['', 'Чистая прибыль (руб., за день)', $metrics['net_profit']],
        ];
    }

    private function weeklyRowsForSection(string $section, array $metrics, ?string $retention): array
    {
        return array_merge($this->dailyRowsForSection($section, $metrics), [
            ['', 'Когортное удержание (%, за неделю, 1/3/7/30 д.)', $retention],
            ['', 'Коэф. виральности (за неделю)', $metrics['virality_rate']],
            ['', 'Коэф. участия (за неделю)', $metrics['participation_rate']],
            ['', '% активных создателей (%, за неделю)', $metrics['active_creators_percent']],
            ['', '% регулярных создателей (%, за неделю)', $metrics['regular_creators_percent']],
            ['', 'Валовый доход (руб., за неделю)', $metrics['gross_revenue']],
            ['', '% доходности (%, за неделю)', $metrics['profitability_percent']],
            ['', 'Прибыльность 1 пользователя (руб., за неделю)', $metrics['profit_per_user']],
        ]);
    }

    private function analyticsRowsForSection(string $section, array $metrics, ?string $retention = null): array
    {
        $rows = [
            [$section, 'DAU (за день)', $metrics['dau']],
            ['', 'Коэф. липкости (%, за день)', $metrics['stickiness']],
            ['', 'Время на платформе (мин., за день)', $metrics['time_on_platform_minutes']],
            ['', 'Число смс за сутки (за день)', $metrics['messages_count']],
        ];

        if ($retention !== null) {
            $rows[] = ['', 'Когортное удержание (%, за неделю, 1/3/7/30 д.)', $retention];
        }

        return $rows;
    }

    private function applicationMetrics(array $report): array
    {
        $database = $this->databaseMetrics($report);

        return array_merge(
            array_fill_keys(array_keys($report['metrics']), null),
            $report['appmetrica'] ?? [],
            ['messages_count' => $database['app_messages_count'] ?? null]
        );
    }

    private function siteMetrics(array $report): array
    {
        $database = $this->databaseMetrics($report);

        return array_merge(
            array_fill_keys(array_keys($report['metrics']), null),
            $report['site'] ?? [],
            ['messages_count' => $database['site_messages_count'] ?? null]
        );
    }

    private function totalMetrics(array $report): array
    {
        $app = array_merge(array_fill_keys(array_keys($report['metrics']), null), $report['appmetrica'] ?? []);
        $site = $this->siteMetrics($report);
        $total = $report['metrics'];

        $total['dau'] = $this->sumValues($app['dau'] ?? null, $site['dau'] ?? null);
        $total['wau'] = $this->sumValues($app['wau'] ?? null, $site['wau'] ?? null);
        $total['sessions'] = $this->sumValues($app['sessions'] ?? null, $site['sessions'] ?? null);
        $total['stickiness'] = ($total['wau'] ?? 0) > 0
            ? round(($total['dau'] ?? 0) / $total['wau'] * 100, 2)
            : null;

        $total['time_on_platform_minutes'] = $this->weightedAverage(
            $app['time_on_platform_minutes'] ?? null,
            $app['dau'] ?? null,
            $site['time_on_platform_minutes'] ?? null,
            $site['dau'] ?? null
        );

        return $total;
    }

    private function sumValues(mixed ...$values): ?float
    {
        $sum = 0.0;
        $hasValue = false;

        foreach ($values as $value) {
            if (!is_numeric($value)) {
                continue;
            }

            $sum += (float) $value;
            $hasValue = true;
        }

        return $hasValue ? $sum : null;
    }

    private function weightedAverage(mixed $leftValue, mixed $leftWeight, mixed $rightValue, mixed $rightWeight): ?float
    {
        $sum = 0.0;
        $weight = 0.0;

        if (is_numeric($leftValue) && is_numeric($leftWeight) && (float) $leftWeight > 0) {
            $sum += (float) $leftValue * (float) $leftWeight;
            $weight += (float) $leftWeight;
        }

        if (is_numeric($rightValue) && is_numeric($rightWeight) && (float) $rightWeight > 0) {
            $sum += (float) $rightValue * (float) $rightWeight;
            $weight += (float) $rightWeight;
        }

        return $weight > 0 ? round($sum / $weight, 2) : null;
    }

    private function databaseMetrics(array $report): array
    {
        return array_merge($report['database'] ?? [], $report['database_daily'] ?? [], $report['database_weekly'] ?? []);
    }

    /**
     * @throws GuzzleException
     */
    public function syncDaily(array $report): array
    {
        return $this->writeDateColumn(
            Carbon::parse($report['period']['date_to']),
            $this->dailyRows($report)
        );
    }

    /**
     * @throws GuzzleException
     */
    public function syncWeekly(array $report): array
    {
        $dateTo = Carbon::parse($report['period']['date_to']);
        $guard = $this->weeklySyncGuard($dateTo);

        if (($guard['skipped'] ?? false) === true) {
            return $guard;
        }

        return $this->writeDateColumn(
            $dateTo,
            $this->weeklyRows($report)
        );
    }

    /**
     * @throws GuzzleException
     */
    public function weeklySyncGuard(Carbon $dateTo): array
    {
        if (!$dateTo->isSunday()) {
            return [
                'skipped' => true,
                'reason' => 'Weekly metrics can be written only to Sunday column: '.$this->dateHeaderLabel($dateTo),
            ];
        }

        $previousDate = $dateTo->copy()->subDays(7);

        if (!$this->hasDateColumn($previousDate)) {
            return [
                'skipped' => true,
                'reason' => 'Previous weekly date column is missing: '.$this->dateHeaderLabel($previousDate),
            ];
        }

        return ['skipped' => false];
    }

    /**
     * @throws GuzzleException
     */
    public function repairTemplate(): array
    {
        $rows = $this->templateRows();
        $wideClear = array_fill(0, 1, array_fill(0, 702, ''));
        $tailStartRow = count($rows) + 3;
        $tailClear = array_fill(0, max(1, 101 - $tailStartRow), array_fill(0, 702, ''));
        $clear = array_fill(0, 99, ['', '']);
        $values = array_map(static fn (array $row): array => [$row[0], $row[1]], $rows);

        $this->client->updateValues(
            $this->sheetName.'!A1:ZZ1',
            $wideClear
        );

        $this->client->updateValues(
            $this->sheetName.'!B2:C100',
            $clear
        );

        $this->client->updateValues(
            $this->sheetName.'!A'.$tailStartRow.':ZZ100',
            $tailClear
        );

        return $this->client->updateValues(
            $this->sheetName.'!B3:C'.(count($values) + 2),
            $this->normalizeValues($values)
        );
    }

    /**
     * @throws GuzzleException
     */
    private function writeDateColumn(Carbon $date, array $rows): array
    {
        $dateLabel = $this->dateHeaderLabel($date);
        $existing = $this->client->getValues($this->sheetName.'!A1:ZZ200')['values'] ?? [];
        $rowMap = $this->rowMap($existing);
        $column = $this->dateColumn($existing[self::HEADER_ROW - 1] ?? [], $dateLabel);

        if ($this->isEmptySheet($existing)) {
            $column = self::FIRST_DATE_COLUMN;
            $this->client->updateValues($this->cell(self::HEADER_ROW, $column), [[$dateLabel]]);
            $this->client->setColumnWidth($this->sheetName, $column, $column, self::DATE_COLUMN_WIDTH);
        } elseif ($column === null) {
            $column = $this->insertionColumn($existing[self::HEADER_ROW - 1] ?? [], $date);
            $this->client->insertColumns($this->sheetName, $column);
            $this->client->updateValues($this->cell(self::HEADER_ROW, $column), [[$dateLabel]]);
            $this->client->setColumnWidth($this->sheetName, $column, $column, self::DATE_COLUMN_WIDTH);
        } else {
            $this->client->setColumnWidth($this->sheetName, $column, $column, self::DATE_COLUMN_WIDTH);
        }

        $nextRow = $rowMap === []
            ? self::HEADER_ROW + 1
            : max($rowMap) + 1;
        $currentSection = 'Общее';
        $valuesByRow = [];

        foreach ($rows as $row) {
            [$section, $metric, $value] = [$row[0], $row[1], $row[2] ?? null];
            if ($section !== '') {
                $currentSection = $section;
            }
            $key = $currentSection.'|'.$metric;

            if (!isset($rowMap[$key])) {
                $rowMap[$key] = $nextRow++;
            }

            $targetRow = $rowMap[$key];
            if ($section !== '') {
                $this->client->updateValues(
                    $this->range($targetRow, self::SECTION_COLUMN, $targetRow, self::METRIC_COLUMN),
                    [[$section, $metric]]
                );
            } elseif (!isset($existing[$targetRow - 1][self::METRIC_COLUMN - 1])) {
                $this->client->updateValues(
                    $this->cell($targetRow, self::METRIC_COLUMN),
                    [[$metric]]
                );
            }

            $valuesByRow[$targetRow] = $this->normalizeValue($value);
        }

        if ($valuesByRow === []) {
            return [];
        }

        ksort($valuesByRow);

        $startRow = min(array_keys($valuesByRow));
        $endRow = max(array_keys($valuesByRow));
        $columnValues = [];

        for ($row = $startRow; $row <= $endRow; $row++) {
            $columnValues[] = [$valuesByRow[$row] ?? ''];
        }

        return $this->client->updateValues(
            $this->range($startRow, $column, $endRow, $column),
            $columnValues
        );
    }

    private function retentionValue(array $report): ?string
    {
        return $this->retentionReportValue($report['retention'] ?? null);
    }

    private function retentionReportValue(?array $retentionReport): ?string
    {
        if (empty($retentionReport['retention'])) {
            return null;
        }

        $parts = [];
        foreach ([1, 3, 7, 30] as $day) {
            $rate = Arr::get($retentionReport, 'retention.'.$day.'.rate');
            $parts[] = $rate === null ? 'н/д' : (string) $rate;
        }

        return implode('/', $parts);
    }

    private function normalizeValues(array $values): array
    {
        return array_map(fn (array $row): array => array_map(fn ($value) => $this->normalizeValue($value), $row), $values);
    }

    private function normalizeValue(mixed $value): mixed
    {
        return $value ?? 'н/д';
    }

    private function rowMap(array $values): array
    {
        $map = [];
        $currentSection = '';

        foreach ($values as $index => $row) {
            if ($index + 1 <= self::HEADER_ROW) {
                continue;
            }

            if (!empty($row[self::SECTION_COLUMN - 1])) {
                $currentSection = (string) $row[self::SECTION_COLUMN - 1];
            }

            $metric = $row[self::METRIC_COLUMN - 1] ?? null;
            if ($metric && $currentSection !== '') {
                $map[$currentSection.'|'.$metric] = $index + 1;
            }
        }

        return $map;
    }

    private function dateColumn(array $header, string $dateLabel): ?int
    {
        foreach ($header as $index => $value) {
            if ($index + 1 >= self::FIRST_DATE_COLUMN && (string) $value === $dateLabel) {
                return $index + 1;
            }
        }

        return null;
    }

    /**
     * @throws GuzzleException
     */
    private function hasDateColumn(Carbon $date): bool
    {
        $existing = $this->client->getValues($this->sheetName.'!A'.self::HEADER_ROW.':ZZ'.self::HEADER_ROW)['values'] ?? [];
        $header = $existing[0] ?? [];

        return $this->dateColumn($header, $this->dateHeaderLabel($date)) !== null;
    }

    private function insertionColumn(array $header, Carbon $date): int
    {
        for ($index = self::FIRST_DATE_COLUMN - 1; $index < count($header); $index++) {
            $headerDate = $this->parseHeaderDate((string) ($header[$index] ?? ''), $date->year);
            if ($headerDate && $headerDate->lt($date->copy()->startOfDay())) {
                return $index + 1;
            }
        }

        return max(self::FIRST_DATE_COLUMN, count($header) + 1);
    }

    private function parseHeaderDate(string $value, int $year): ?Carbon
    {
        if (!preg_match('/^(\d{2})\.(\d{2})\./', $value, $matches)) {
            return null;
        }

        return Carbon::createFromDate($year, (int) $matches[2], (int) $matches[1])->startOfDay();
    }

    private function dateHeaderLabel(Carbon $date): string
    {
        $weekdays = [
            1 => 'пн',
            2 => 'вт',
            3 => 'ср',
            4 => 'чт',
            5 => 'пт',
            6 => 'сб',
            7 => 'вс',
        ];

        return $date->format('d.m.').' '.$weekdays[$date->isoWeekday()];
    }

    private function isEmptySheet(array $values): bool
    {
        return $values === [] || ($values === [[]]);
    }

    private function cell(int $row, int $column): string
    {
        return $this->sheetName.'!'.$this->columnName($column).$row;
    }

    private function range(int $startRow, int $startColumn, int $endRow, int $endColumn): string
    {
        return sprintf(
            '%s!%s%d:%s%d',
            $this->sheetName,
            $this->columnName($startColumn),
            $startRow,
            $this->columnName($endColumn),
            $endRow
        );
    }

    private function columnName(int $column): string
    {
        $name = '';

        while ($column > 0) {
            $column--;
            $name = chr(65 + ($column % 26)).$name;
            $column = intdiv($column, 26);
        }

        return $name;
    }

    private function rectangular(array $values, ?int $width = null): array
    {
        $width ??= max(array_map('count', $values ?: [[]]));

        return array_map(static function (array $row) use ($width): array {
            return array_pad($row, $width, null);
        }, $values);
    }

    private function ensureCell(array &$values, int $row, int $column): void
    {
        while (count($values) < $row) {
            $values[] = [];
        }

        if (count($values[$row - 1]) < $column) {
            $values[$row - 1] = array_pad($values[$row - 1], $column, null);
        }
    }

    private function sheetValues(array $values): array
    {
        return array_map(static fn (array $row): array => array_map(static fn ($value) => $value ?? '', $row), $values);
    }
}
