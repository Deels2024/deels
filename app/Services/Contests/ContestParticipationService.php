<?php

declare(strict_types=1);

namespace App\Services\Contests;

use App\Helpers\AppHelper;
use App\Models\Battle;
use App\Models\Challenge;
use App\Models\User;
use App\Models\Story;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;

class ContestParticipationService
{
    public function state(Model $contest, string $type, ?int $userId): array
    {
        $finished = (bool) $contest->finished;
        $status = $userId ? $this->storedStatus($type, (int) $contest->id, $userId) : null;
        $hasResult = $userId ? $this->resultQuery($contest, $type, $userId)->exists() : false;
        $isBattleOwner = $type === 'battle'
            && $userId
            && (int) $contest->user_id === $userId;
        $participating = $status !== 'withdrawn'
            && $status !== 'declined'
            && ($hasResult || $status === 'active' || $isBattleOwner);
        $singleAuthor = $type === 'challenge'
            && $participating
            && (int) $contest->participants_count === 1
            && (bool) $contest->started;
        $called = $type === 'battle'
            && $userId
            && (int) $contest->called_user_id === $userId
            && !$participating
            && $status !== 'declined';
        $recruitmentOpen = !$finished
            && $this->canJoin($contest, $type, $userId, $status);

        if ($finished) {
            $label = 'Завершен';
            $action = 'disabled';
        } elseif ($called) {
            $label = 'Принять';
            $action = 'accept';
        } elseif ($participating) {
            $label = 'Выйти из участия';
            $action = 'leave';
        } elseif (!$recruitmentOpen) {
            $label = 'Набор закрыт';
            $action = 'disabled';
        } elseif ($status === 'withdrawn' && $hasResult) {
            $label = 'Участвовать';
            $action = 'rejoin';
        } else {
            $label = 'Участвовать';
            $action = 'join';
        }

        return compact(
            'label',
            'action',
            'participating',
            'called',
            'finished',
            'singleAuthor',
            'recruitmentOpen',
            'hasResult'
        );
    }

    public function leave(Model $contest, string $type, int $userId): void
    {
        $state = $this->state($contest, $type, $userId);
        if ($state['finished'] || !$state['participating']) {
            throw ValidationException::withMessages(['participation' => 'Участие уже прекращено']);
        }

        DB::transaction(function () use ($contest, $type, $userId): void {
            $this->setStatus($type, (int) $contest->id, $userId, 'withdrawn');
            $this->resultQuery($contest, $type, $userId)->update(['withdrawn_at' => now()]);

            if ($type === 'battle') {
                $contest->forceFill([
                    'loser_user_id' => $userId,
                    'finished' => true,
                ])->saveQuietly();
            } elseif ((int) $contest->participants_count === 1 && (bool) $contest->started) {
                $contest->forceFill([
                    'finished' => true,
                ])->saveQuietly();
            }
        });
    }

    public function rejoin(Model $contest, string $type, int $userId): void
    {
        $state = $this->state($contest, $type, $userId);
        if ($state['finished'] || !$state['recruitmentOpen'] || !$state['hasResult']) {
            throw ValidationException::withMessages(['participation' => 'Набор участников закрыт']);
        }

        DB::transaction(function () use ($contest, $type, $userId): void {
            $this->setStatus($type, (int) $contest->id, $userId, 'active');
            $this->resultQuery($contest, $type, $userId)->update(['withdrawn_at' => null]);
        });
    }

    public function join(Model $contest, string $type, int $userId): void
    {
        $state = $this->state($contest, $type, $userId);
        if ($state['finished'] || !$state['recruitmentOpen'] || $state['participating']) {
            throw ValidationException::withMessages(['participation' => 'Набор участников закрыт']);
        }

        $this->setStatus($type, (int) $contest->id, $userId, 'active');
    }

    public function accept(Battle $battle, int $userId): void
    {
        $state = $this->state($battle, 'battle', $userId);
        if ($state['finished'] || !$state['called']) {
            throw ValidationException::withMessages(['participation' => 'Вызов уже обработан']);
        }

        $this->setStatus('battle', (int) $battle->id, $userId, 'active');
    }

    public function decline(Battle $battle, int $userId): void
    {
        $state = $this->state($battle, 'battle', $userId);
        if ($state['finished'] || !$state['called']) {
            throw ValidationException::withMessages(['participation' => 'Отказ уже обработан']);
        }

        DB::transaction(function () use ($battle, $userId): void {
            $this->setStatus('battle', (int) $battle->id, $userId, 'declined');
            if (Schema::hasTable('contest_notification_deliveries')) {
                DB::table('contest_notification_deliveries')
                    ->where([
                        'contest_type' => 'battle',
                        'contest_id' => $battle->id,
                        'user_id' => $userId,
                        'kind' => 'call',
                    ])
                    ->delete();
            }
            $battle->forceFill([
                'called_user_id' => null,
            ])->saveQuietly();
        });

        $declinedUser = User::find($userId);
        $declinedName = $declinedUser ? ($declinedUser->fullname ?: $declinedUser->name) : 'Пользователь';
        (new AppHelper())->chat_notify(
            $battle->user,
            sprintf('Пользователь %s отказался от участия в батле %s', $declinedName, $battle->title),
            null
        );
        if ($declinedUser) {
            app(ContestNotificationService::class)->battleCallDeclined($battle, $declinedUser);
        }
    }

    private function canJoin(Model $contest, string $type, ?int $userId, ?string $status): bool
    {
        if (!$userId || $status === 'declined') {
            return false;
        }

        if ($type === 'battle' && (!$userId || (int) $contest->called_user_id !== $userId)) {
            return false;
        }

        $limit = (int) ($contest->participants_count ?? 0);
        if ($limit > 0) {
            if ($this->activeParticipantsCount($contest, $type) >= $limit) {
                return false;
            }
        }

        return true;
    }

    private function activeParticipantsCount(Model $contest, string $type): int
    {
        $resultParticipantIds = $this->resultQuery($contest, $type)
            ->whereNull('withdrawn_at')
            ->pluck('user_id')
            ->filter()
            ->map(static fn ($id): int => (int) $id);

        if (Schema::hasTable('contest_participations')) {
            $storedParticipantIds = DB::table('contest_participations')
                ->where([
                    'contest_type' => $type,
                    'contest_id' => $contest->id,
                    'status' => 'active',
                ])
                ->pluck('user_id')
                ->map(static fn ($id): int => (int) $id);

            $resultParticipantIds = $resultParticipantIds->merge($storedParticipantIds);
        }

        return $resultParticipantIds->unique()->count();
    }

    private function resultQuery(Model $contest, string $type, ?int $userId = null)
    {
        $query = Story::withoutGlobalScopes()
            ->where($type . '_id', $contest->id)
            ->notMainStory();

        return $userId ? $query->where('user_id', $userId) : $query;
    }

    private function storedStatus(string $type, int $contestId, int $userId): ?string
    {
        return DB::table('contest_participations')
            ->where([
                'contest_type' => $type,
                'contest_id' => $contestId,
                'user_id' => $userId,
            ])
            ->value('status');
    }

    private function setStatus(string $type, int $contestId, int $userId, string $status): void
    {
        DB::table('contest_participations')->updateOrInsert(
            [
                'contest_type' => $type,
                'contest_id' => $contestId,
                'user_id' => $userId,
            ],
            [
                'status' => $status,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }
}
