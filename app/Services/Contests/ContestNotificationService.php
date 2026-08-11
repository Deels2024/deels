<?php

declare(strict_types=1);

namespace App\Services\Contests;

use App\Jobs\NotifyAllChannels;
use App\Models\Battle;
use App\Models\Challenge;
use App\Models\User;
use App\Services\UserEventService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ContestNotificationService
{
    public function challengePublished(Challenge $challenge): void
    {
        $this->publish($challenge, 'challenge');
    }

    public function battleModerated(Battle $battle): void
    {
        $calledUserId = (int) $battle->called_user_id;
        if (! $calledUserId || ! $this->claimDelivery('battle', (int) $battle->id, $calledUserId, 'call')) {
            return;
        }

        $author = $battle->user;
        $message = sprintf(
            'Батл! %s бросил вам вызов! “%s”! Участвуйте!',
            $this->link(route('user.profile', $author->id), $author->name),
            $this->link(route('battle_page', $battle->id), $battle->title)
        );

        NotifyAllChannels::dispatch($calledUserId, $message, 'Вас вызвали на батл');
    }

    public function battleAccepted(Battle $battle): void
    {
        if ($this->publicationVersion('battle', (int) $battle->id) === 0) {
            $this->publish($battle, 'battle');
        }
    }

    public function battleCallDeclined(Battle $battle, User $declinedUser): void
    {
        $name = $declinedUser->fullname ?: $declinedUser->name;
        $message = sprintf(
            'Пользователь %s отказался от участия в батле %s',
            e((string) $name),
            $this->link(route('battle_page', $battle->id), $battle->title)
        );

        NotifyAllChannels::dispatch((int) $battle->user_id, $message, 'Вызов отклонен');
    }

    public function battleUpdated(Battle $battle): void
    {
        $this->battleModerated($battle);

        if ($this->publicationVersion('battle', (int) $battle->id) > 0) {
            $this->publish($battle, 'battle');
        }
    }

    public function results(Model $contest, string $type, Collection $stories, array $winnerStoryIds, int $prize, array $winnerUserIdsOverride = []): void
    {
        $winnerStoryIds = $this->ids($winnerStoryIds);
        $winnerUserIds = $this->ids($winnerUserIdsOverride ?: $stories->whereIn('id', $winnerStoryIds)->pluck('user_id')->all());
        $participantUserIds = $this->ids($stories->pluck('user_id')->all());
        $participantUserIds = $this->ids(array_merge($participantUserIds, $winnerUserIds));

        if ($type === 'battle') {
            $participantUserIds = $this->ids(array_merge($participantUserIds, [
                $contest->user_id,
                $contest->called_user_id,
            ]));

            if ($contest->completion_status === 'draw') {
                $this->notifyBattleDraw($contest, $participantUserIds);

                return;
            }

            if ($contest->completion_status === 'skipped') {
                $this->notifySkipped($contest, 'battle', $participantUserIds);

                return;
            }
        } elseif ($contest->completion_status === 'skipped') {
            $this->notifySkipped($contest, 'challenge', $participantUserIds ?: [(int) $contest->user_id]);

            return;
        }

        foreach ($participantUserIds as $userId) {
            $isWinner = in_array($userId, $winnerUserIds, true);
            if ($isWinner) {
                $opponent = $type === 'battle'
                    ? $this->battleOpponent($contest, $userId, $participantUserIds)
                    : null;
                app(UserEventService::class)->contestResult(
                    $userId,
                    $contest,
                    $type,
                    $type === 'challenge' ? 'challenge_win' : 'battle_win',
                    $prize,
                    $opponent
                );
            }

            if (! $this->claimDelivery($type, (int) $contest->id, $userId, 'result')) {
                continue;
            }

            if ($isWinner) {
                $label = $type === 'challenge' ? 'челлендже' : 'батле';
                $message = sprintf(
                    'Потрясающая работа! Вы победили в %s “%s”',
                    $label,
                    e((string) $contest->title)
                );
                if ($prize > 0) {
                    $message .= '. Ваш приз уже начислен! Скорее используйте его!';
                }

                NotifyAllChannels::dispatch($userId, $message, 'Поздравляем с победой');
            } else {
                NotifyAllChannels::dispatch(
                    $userId,
                    $this->lossMessage($contest, $type, $userId),
                    'Соревнование завершено'
                );
            }
        }
    }

    public function notifyNewContestInvitees(Model $contest, string $type): void
    {
        $this->notifyNewInvitees($contest, $type);
    }

    private function publish(Model $contest, string $type): void
    {
        $version = DB::transaction(function () use ($contest, $type): int {
            $row = DB::table('contest_notification_publications')
                ->where(['contest_type' => $type, 'contest_id' => $contest->id])
                ->lockForUpdate()
                ->first();

            if (! $row) {
                DB::table('contest_notification_publications')->insert([
                    'contest_type' => $type,
                    'contest_id' => $contest->id,
                    'version' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                return 1;
            }

            $version = (int) $row->version + 1;
            DB::table('contest_notification_publications')
                ->where('id', $row->id)
                ->update(['version' => $version, 'updated_at' => now()]);

            return $version;
        });

        if ($version > 1) {
            $this->notifyParticipantsAboutUpdate($contest, $type, $version);
        }

        $this->notifyNewInvitees($contest, $type);
    }

    private function notifyNewInvitees(Model $contest, string $type): void
    {
        foreach ($this->ids($contest->invite_user_ids ?? []) as $userId) {
            if (! $this->claimDelivery($type, (int) $contest->id, $userId, 'invite')) {
                continue;
            }

            $message = $type === 'challenge'
                ? sprintf(
                    'Вас позвали в челлендж “%s”!',
                    $this->link(route('challenge_page', $contest->id), $contest->title)
                )
                : $this->battleInviteMessage($contest);

            NotifyAllChannels::dispatch(
                $userId,
                $message,
                $type === 'challenge' ? 'Приглашение в челлендж' : 'Приглашение на батл'
            );
        }
    }

    private function notifyParticipantsAboutUpdate(Model $contest, string $type, int $version): void
    {
        $label = $type === 'challenge' ? 'Челлендж' : 'Батл';
        $route = $type === 'challenge' ? 'challenge_page' : 'battle_page';
        $message = sprintf(
            '%s “%s” был изменен',
            $label,
            $this->link(route($route, $contest->id), $contest->title)
        );

        foreach ($this->participantIds($contest, $type) as $userId) {
            if ($this->claimDelivery($type, (int) $contest->id, $userId, 'update:'.$version)) {
                NotifyAllChannels::dispatch($userId, $message, $label.' изменен');
            }
        }
    }

    private function battleInviteMessage(Battle $battle): string
    {
        $author = $battle->user;
        $called = \App\Models\User::withTrashed()->find($battle->called_user_id);
        $calledName = $called?->name ?: 'Пользователь удален';

        return sprintf(
            'Батл! %s против %s! “%s”! Заходите посмотреть!',
            $this->link(route('user.profile', $author->id), $author->name),
            $this->link(route('user.profile', $battle->called_user_id), $calledName),
            $this->link(route('battle_page', $battle->id), $battle->title)
        );
    }

    private function notifyBattleDraw(Battle $battle, array $participantUserIds): void
    {
        $names = User::withTrashed()->whereIn('id', $participantUserIds)->pluck('name', 'id');

        foreach ($participantUserIds as $userId) {
            $opponentId = collect($participantUserIds)->first(fn ($id) => $id !== $userId);
            $opponentName = $names[$opponentId] ?? 'сопернику';
            $opponent = $opponentId ? User::withTrashed()->find($opponentId) : null;
            app(UserEventService::class)->contestResult(
                $userId,
                $battle,
                'battle',
                'battle_draw',
                0,
                $opponent
            );

            if (! $this->claimDelivery('battle', (int) $battle->id, $userId, 'result')) {
                continue;
            }

            $message = sprintf(
                'Ничья в батле “%s”. Киньте ответный вызов %s, чтобы определить лучшего!',
                e((string) $battle->title),
                e((string) $opponentName)
            );

            NotifyAllChannels::dispatch($userId, $message, 'Ничья в батле');
        }
    }

    private function notifySkipped(Model $contest, string $type, array $participantUserIds): void
    {
        $label = $type === 'battle' ? 'Батл' : 'Челлендж';

        foreach ($this->ids($participantUserIds) as $userId) {
            if (! $this->claimDelivery($type, (int) $contest->id, $userId, 'result')) {
                continue;
            }

            NotifyAllChannels::dispatch(
                $userId,
                sprintf('%s “%s” пропущен', $label, e((string) $contest->title)),
                $label.' пропущен'
            );
        }
    }

    private function lossMessage(Model $contest, string $type, int $userId): string
    {
        $label = $type === 'challenge' ? 'Челлендж' : 'Батл';
        $contestRoute = $type === 'challenge' ? 'challenge_page' : 'battle_page';
        $suggestion = $this->randomAvailableChallenge($userId);
        $suggestionUrl = $suggestion
            ? route('challenge_page', $suggestion->id)
            : route('challenges.catalog');

        return sprintf(
            '%s “%s” завершен. В этот раз вам не повезло, но попробуйте одержать верх в %s',
            $label,
            $this->link(route($contestRoute, $contest->id), $contest->title),
            $this->link($suggestionUrl, 'другом челлендже')
        );
    }

    private function randomAvailableChallenge(int $userId): ?Challenge
    {
        $participation = app(ContestParticipationService::class);

        return Challenge::query()
            ->active()
            ->where('user_id', '<>', $userId)
            ->where(function ($query): void {
                $query->whereNull('participants_count')->orWhere('participants_count', '<>', 1);
            })
            ->inRandomOrder()
            ->limit(50)
            ->get()
            ->first(fn (Challenge $challenge) => $participation->state(
                $challenge,
                'challenge',
                $userId
            )['recruitmentOpen']);
    }

    private function participantIds(Model $contest, string $type): array
    {
        $ids = [(int) $contest->user_id];
        if ($type === 'battle' && $contest->called_user_id) {
            $ids[] = (int) $contest->called_user_id;
        }

        $pivot = $type.'_user';
        if (Schema::hasTable($pivot)) {
            $ids = array_merge($ids, DB::table($pivot)
                ->where($type.'_id', $contest->id)
                ->pluck('user_id')
                ->all());
        }

        if (Schema::hasTable('stories')) {
            $ids = array_merge($ids, DB::table('stories')
                ->where($type.'_id', $contest->id)
                ->where(function ($query): void {
                    $query->where('is_main_story', false)->orWhereNull('is_main_story');
                })
                ->pluck('user_id')
                ->all());
        }

        return $this->ids($ids);
    }

    private function claimDelivery(string $type, int $contestId, int $userId, string $kind): bool
    {
        return DB::table('contest_notification_deliveries')->insertOrIgnore([
            'contest_type' => $type,
            'contest_id' => $contestId,
            'user_id' => $userId,
            'kind' => $kind,
            'created_at' => now(),
            'updated_at' => now(),
        ]) === 1;
    }

    private function publicationVersion(string $type, int $contestId): int
    {
        return (int) DB::table('contest_notification_publications')
            ->where(['contest_type' => $type, 'contest_id' => $contestId])
            ->value('version');
    }

    private function ids(array $ids): array
    {
        return collect($ids)->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
    }

    private function battleOpponent(Battle $battle, int $userId, array $participantUserIds): ?User
    {
        $opponentId = collect([
            (int) $battle->user_id,
            (int) $battle->called_user_id,
            ...$participantUserIds,
        ])->first(fn (int $id): bool => $id > 0 && $id !== $userId);

        return $opponentId ? User::withTrashed()->find($opponentId) : null;
    }

    private function link(string $url, ?string $label): string
    {
        return sprintf('<a href="%s">%s</a>', e($url), e((string) $label));
    }
}
