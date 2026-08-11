<?php

declare(strict_types=1);

namespace App\Services\Metrics;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class DatabaseMetricsService
{
    public function daily(Carbon $dateFrom, Carbon $dateTo, ?int $dau = null): array
    {
        $appSms = $this->smsCount($dateFrom, $dateTo, 'sms');
        $siteSms = $this->smsCount($dateFrom, $dateTo, 'sms_web');
        $messages = $appSms + $siteSms;
        $chatUsers = $this->distinctCount('messages', 'user_id', $dateFrom, $dateTo);
        $activeStories = $this->activeStories($dateFrom, $dateTo);
        $interactions = $this->interactions($dateFrom, $dateTo);
        $internalEconomy = $this->internalEconomy($dateFrom, $dateTo);
        $expense = 0.0;

        return [
            'interactions_count' => $interactions,
            'active_stories' => $activeStories,
            'avg_interactions' => $activeStories === 0 ? null : round($interactions / $activeStories, 2),
            'messages_count' => $messages,
            'app_messages_count' => $appSms,
            'site_messages_count' => $siteSms,
            'chat_users' => $chatUsers,
            'chatting_users_percent' => $dau && $dau > 0 ? round($chatUsers / $dau * 100, 2) : null,
            'internal_economy' => $internalEconomy,
            'expense' => $expense,
            'net_profit' => round($this->grossRevenue($dateFrom, $dateTo) - $expense, 2),
        ];
    }

    public function weekly(Carbon $dateFrom, Carbon $dateTo, ?int $wau = null): array
    {
        $grossRevenue = $this->grossRevenue($dateFrom, $dateTo);
        $internalEconomy = $this->internalEconomy($dateFrom, $dateTo);
        $activeCreators = $this->activeCreators($dateFrom, $dateTo);
        $regularCreators = $this->regularCreators($dateFrom, $dateTo);

        return [
            'gross_revenue' => $grossRevenue,
            'profitability_percent' => $internalEconomy > 0 ? round($grossRevenue / $internalEconomy * 100, 2) : null,
            'active_creators' => $activeCreators,
            'active_creators_percent' => $wau && $wau > 0 ? round($activeCreators / $wau * 100, 2) : null,
            'regular_creators' => $regularCreators,
            'regular_creators_percent' => $activeCreators > 0 ? round($regularCreators / $activeCreators * 100, 2) : null,
            'participation_rate' => $this->participationRate($dateFrom, $dateTo),
            'virality_rate' => null,
            'profit_per_user' => null,
        ];
    }

    private function count(string $table, Carbon $dateFrom, Carbon $dateTo): int
    {
        return (int) DB::table($table)
            ->whereBetween($table.'.created_at', [$dateFrom, $dateTo])
            ->count();
    }

    private function smsCount(Carbon $dateFrom, Carbon $dateTo, string $type): int
    {
        return (int) DB::table('logs')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('type', $type)
            ->count();
    }

    private function distinctCount(string $table, string $column, Carbon $dateFrom, Carbon $dateTo): int
    {
        return (int) DB::table($table)
            ->whereBetween($table.'.created_at', [$dateFrom, $dateTo])
            ->whereNotNull($column)
            ->where($column, '!=', 0)
            ->distinct()
            ->count($column);
    }

    private function activeStories(Carbon $dateFrom, Carbon $dateTo): int
    {
        return (int) DB::table('stories')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->where('active', 1)
            ->where('declined', 0)
            ->whereNull('challenge_id')
            ->count();
    }

    private function interactions(Carbon $dateFrom, Carbon $dateTo): int
    {
        $likes = $this->count('likes', $dateFrom, $dateTo);
        $comments = $this->count('comments', $dateFrom, $dateTo);
        $donates = (int) DB::table('transactions')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->where('transactions.amount', '<', 0)
            ->where(function ($query): void {
                $query->where('meta', 'like', '%"description":"Донат в сторис%')
                    ->orWhere('meta', 'like', '%"donate":"campaign","description":"Оплата копилки%');
            })
            ->count();

        return $likes + $comments + $donates;
    }

    private function internalEconomy(Carbon $dateFrom, Carbon $dateTo): float
    {
        return abs((float) DB::table('transactions')
            ->leftJoin('wallets', 'wallets.id', '=', 'transactions.wallet_id')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->where('transactions.amount', '<', 0)
            ->where(function ($query): void {
                $query->where('transactions.meta', 'like', '%"description":"Донат в сторис%')
                    ->orWhere('transactions.meta', 'like', '%"donate":"campaign","description":"Оплата копилки%')
                    ->orWhere('transactions.meta', 'like', '%"description":"Оплата за хранение сторис%')
                    ->orWhere('transactions.meta', 'like', '%"description":"Оплата за использование ИИ%')
                    ->orWhere('transactions.meta', 'like', '%"description":"Победа в челлендже%');
            })
            ->sum($this->rubAmountExpression()));
    }

    private function grossRevenue(Carbon $dateFrom, Carbon $dateTo): float
    {
        return abs((float) DB::table('transactions')
            ->leftJoin('wallets', 'wallets.id', '=', 'transactions.wallet_id')
            ->whereBetween('transactions.created_at', [$dateFrom, $dateTo])
            ->where('transactions.amount', '<', 0)
            ->where(function ($query): void {
                $query->where('transactions.meta', 'like', '%"description":"Оплата за хранение сторис%')
                    ->orWhere('transactions.meta', 'like', '%"description":"Оплата за использование ИИ%');
            })
            ->sum($this->rubAmountExpression()));
    }

    private function rubAmountExpression(): \Illuminate\Database\Query\Expression
    {
        return DB::raw(
            "CASE WHEN wallets.slug = 'payments' THEN transactions.amount / 100 ELSE transactions.amount END"
        );
    }

    private function activeCreators(Carbon $dateFrom, Carbon $dateTo): int
    {
        return $this->distinctCount('stories', 'user_id', $dateFrom, $dateTo);
    }

    private function regularCreators(Carbon $dateFrom, Carbon $dateTo): int
    {
        $query = DB::table('stories')
            ->select('user_id')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) >= 3');

        return (int) DB::query()->fromSub($query, 'regular_creators')->count();
    }

    private function participationRate(Carbon $dateFrom, Carbon $dateTo): ?float
    {
        $days = [];
        for ($date = $dateFrom->copy()->startOfDay(); $date->lte($dateTo); $date->addDay()) {
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();
            $challenges = (int) DB::table('challenges')->whereBetween('created_at', [$dayStart, $dayEnd])->count();

            if ($challenges === 0) {
                continue;
            }

            $stories = (int) DB::table('stories')
                ->whereBetween('created_at', [$dayStart, $dayEnd])
                ->whereNotNull('challenge_id')
                ->count();

            $days[] = $stories / $challenges;
        }

        return $days ? round(array_sum($days) / count($days), 2) : null;
    }
}
