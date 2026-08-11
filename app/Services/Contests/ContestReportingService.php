<?php

declare(strict_types=1);

namespace App\Services\Contests;

use App\Models\ContestReport;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ContestReportingService
{
    public function __construct(private ContestParticipationService $participation)
    {
    }

    public function state(Model $contest, string $type, int $userId, ?Carbon $now = null): array
    {
        $now ??= Carbon::now();
        $period = $this->period($contest, $type, $now);
        $participationState = $this->participation->state($contest, $type, $userId);
        $participating = $participationState['participating'];
        if ($type === 'battle') {
            $isAuthor = (int) $contest->user_id === $userId;
            $isStartedOpponent = (int) $contest->called_user_id === $userId
                && (bool) $contest->started;
            $participating = $participating || $isAuthor || $isStartedOpponent;
        }
        $available = $participating && $this->isRunning($contest, $now);
        $visible = $participating && $this->isOpenOrUpcoming($contest, $now);
        $reports = $visible
            ? ContestReport::with('story')
                ->where([
                    'contest_type' => $type,
                    'contest_id' => $contest->id,
                    'user_id' => $userId,
                ])
                ->orderByDesc('created_at')
                ->get()
            : collect();
        $periodReports = $reports->filter(
            fn (ContestReport $report) => $report->period_started_at->equalTo($period['start'])
        );
        $limit = $this->storyLimit($contest, $type);
        $storyCount = $periodReports->where('kind', 'story')->whereNotNull('story_id')->count();

        return [
            'visible' => $visible,
            'available' => $available,
            'checkin' => (string) ($contest->checkin ?: 'story'),
            'period_start' => $period['start'],
            'period_end' => $period['end'],
            'reports' => $reports,
            'button_done' => $periodReports->where('kind', 'button')->isNotEmpty(),
            'value' => optional($periodReports->firstWhere('kind', 'value'))->value,
            'story_limit' => $limit,
            'story_count' => $storyCount,
            'story_allowed' => $available && $storyCount < $limit,
            'total' => $reports->filter(
                fn (ContestReport $report) => $report->kind !== 'story' || $report->story_id !== null
            )->sum(fn (ContestReport $report) => $report->kind === 'value' ? (float) $report->value : 1),
        ];
    }

    public function submit(Model $contest, string $type, int $userId, string $kind, ?float $value = null): array
    {
        $this->assertCanReport($contest, $type, $userId, $kind);
        $period = $this->period($contest, $type, Carbon::now());

        return DB::transaction(function () use ($contest, $type, $userId, $kind, $value, $period): array {
            $query = ContestReport::where([
                'contest_type' => $type,
                'contest_id' => $contest->id,
                'user_id' => $userId,
                'kind' => $kind,
                'period_started_at' => $period['start'],
            ])->lockForUpdate();
            $existing = $query->first();

            if ($kind === 'button' && $existing) {
                throw ValidationException::withMessages(['report' => 'Отметка за этот период уже добавлена']);
            }

            if ($existing) {
                $existing->update(['value' => $value]);
                return ['report' => $existing, 'updated' => true];
            }

            return [
                'report' => ContestReport::create([
                    'contest_type' => $type,
                    'contest_id' => $contest->id,
                    'user_id' => $userId,
                    'kind' => $kind,
                    'value' => $value,
                    'period_started_at' => $period['start'],
                    'period_ended_at' => $period['end'],
                ]),
                'updated' => false,
            ];
        });
    }

    public function attachStory(Model $contest, string $type, int $userId, int $storyId): ContestReport
    {
        $this->assertCanReport($contest, $type, $userId, 'story');
        $period = $this->period($contest, $type, Carbon::now());

        return DB::transaction(function () use ($contest, $type, $userId, $storyId, $period): ContestReport {
            $count = ContestReport::where([
                'contest_type' => $type,
                'contest_id' => $contest->id,
                'user_id' => $userId,
                'kind' => 'story',
                'period_started_at' => $period['start'],
            ])->whereNotNull('story_id')->lockForUpdate()->count();

            if ($count >= $this->storyLimit($contest, $type)) {
                throw ValidationException::withMessages(['report' => 'Лимит сторис за этот период исчерпан']);
            }

            return ContestReport::create([
                'contest_type' => $type,
                'contest_id' => $contest->id,
                'user_id' => $userId,
                'kind' => 'story',
                'story_id' => $storyId,
                'period_started_at' => $period['start'],
                'period_ended_at' => $period['end'],
            ]);
        });
    }

    private function assertCanReport(Model $contest, string $type, int $userId, string $kind): void
    {
        $state = $this->state($contest, $type, $userId);
        if (!$state['available']) {
            throw ValidationException::withMessages(['report' => 'Отчётность сейчас недоступна']);
        }
        if ($state['checkin'] !== $kind) {
            throw ValidationException::withMessages(['report' => 'Неверный тип отчётности']);
        }
        if ($kind === 'story' && !$state['story_allowed']) {
            throw ValidationException::withMessages(['report' => 'Лимит сторис за этот период исчерпан']);
        }
    }

    private function isRunning(Model $contest, Carbon $now): bool
    {
        $start = $contest->date_from ?: $contest->start ?: $contest->created_at;
        $end = $contest->date_to ?: $contest->finish;

        return (bool) $contest->active
            && !$contest->declined
            && !$contest->finished
            && (!$start || $now->greaterThanOrEqualTo(Carbon::parse($start)->startOfDay()))
            && (!$end || $now->lessThanOrEqualTo(Carbon::parse($end)->endOfDay()));
    }

    private function isOpenOrUpcoming(Model $contest, Carbon $now): bool
    {
        $end = $contest->date_to ?: $contest->finish;

        return (bool) $contest->active
            && !$contest->declined
            && !$contest->finished
            && (!$end || $now->lessThanOrEqualTo(Carbon::parse($end)->endOfDay()));
    }

    private function period(Model $contest, string $type, Carbon $now): array
    {
        $start = Carbon::parse($contest->date_from ?: $contest->start ?: $contest->created_at)->startOfDay();
        $rhythm = $type === 'battle' ? 'daily' : ($contest->rhythm ?: 'daily');
        if ($rhythm === 'once') {
            return [
                'start' => $start,
                'end' => Carbon::parse($contest->date_to ?: $contest->finish)->endOfDay(),
            ];
        }

        $days = $rhythm === 'three_days' ? 3 : 1;
        $elapsedDays = max(0, $start->diffInDays($now->copy()->startOfDay(), false));
        $periodStart = $start->copy()->addDays(intdiv($elapsedDays, $days) * $days);

        return [
            'start' => $periodStart,
            'end' => $periodStart->copy()->addDays($days)->subSecond(),
        ];
    }

    private function storyLimit(Model $contest, string $type): int
    {
        if (($contest->rhythm ?? null) === 'once') {
            return 1;
        }
        if ($type === 'battle' || (int) ($contest->participants_count ?? 0) === 1) {
            return 10;
        }

        return 1;
    }
}
