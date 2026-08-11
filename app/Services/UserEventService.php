<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\UserEvent;
use Illuminate\Database\Eloquent\Model;

class UserEventService
{
    public function contestResult(
        int $userId,
        Model $contest,
        string $contestType,
        string $result,
        int $rewardAmount = 0,
        ?User $opponent = null
    ): UserEvent {
        $contestUrl = route($contestType === 'challenge' ? 'challenge_page' : 'battle_page', $contest->id);
        $opponentName = $opponent ? ($opponent->fullname ?: $opponent->name) : null;

        return UserEvent::firstOrCreate(
            [
                'user_id' => $userId,
                'type' => 'contest_result',
                'source_type' => $contestType,
                'source_id' => (int) $contest->id,
                'result' => $result,
            ],
            [
                'data' => [
                    'title' => $result === 'battle_draw' ? 'Ничья!' : 'Победа!',
                    'message' => $this->message(
                        $contestType,
                        $result,
                        (string) $contest->title,
                        $contestUrl,
                        $opponent,
                        $opponentName
                    ),
                    'contest' => [
                        'type' => $contestType,
                        'id' => (int) $contest->id,
                        'title' => (string) $contest->title,
                        'url' => $contestUrl,
                    ],
                    'opponent' => $opponent ? [
                        'id' => (int) $opponent->id,
                        'name' => $opponentName,
                        'url' => route('user.profile', $opponent->id),
                    ] : null,
                    'reward_amount' => $rewardAmount > 0 ? $rewardAmount : null,
                    'reward_text' => $rewardAmount > 0 ? 'Ваша награда: '.$rewardAmount : null,
                    'presentation' => [
                        'background' => 'fireworks',
                        'closable' => true,
                    ],
                ],
                'expires_at' => now()->addDays(14),
            ]
        );
    }

    private function message(
        string $contestType,
        string $result,
        string $contestTitle,
        string $contestUrl,
        ?User $opponent,
        ?string $opponentName
    ): string {
        $contestLink = $this->link($contestUrl, $contestTitle);

        if ($contestType === 'challenge') {
            return 'Вы успешно прошли челлендж “'.$contestLink.'”!';
        }

        $opponentLink = $opponent
            ? $this->link(route('user.profile', $opponent->id), (string) $opponentName)
            : e((string) $opponentName);

        if ($result === 'battle_draw') {
            return 'Мастерство '.$opponentLink.' оказалось наравне с вашим в батле “'.$contestLink.'”!';
        }

        return 'Вы одержали верх над: '.$opponentLink.' в батле “'.$contestLink.'”';
    }

    private function link(string $url, string $label): string
    {
        return sprintf('<a href="%s">%s</a>', e($url), e($label));
    }
}
