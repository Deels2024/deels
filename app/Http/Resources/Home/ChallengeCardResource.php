<?php

declare(strict_types=1);

namespace App\Http\Resources\Home;

use App\Services\Home\HomeMediaResolver;

final class ChallengeCardResource extends HomeCardResource
{
    public function toArray($request): array
    {
        $currentParticipants = $this->count($this->resource, 'stories_count', 'stories');
        $minimumParticipants = (int) ($this->min_participants ?? 0);
        $statusTitle = 'Длится';
        if ($this->finished) {
            $statusTitle = $this->completion_status === 'skipped' ? 'Пропущен' : 'Завершен';
        } elseif ($this->frozen) {
            $statusTitle = 'Заморожен';
        } elseif (!$this->started) {
            $statusTitle = $minimumParticipants <= 0 || $currentParticipants >= $minimumParticipants
                ? 'Запланирован'
                : 'Идет набор';
        }

        return [
            'id' => (int) $this->id,
            'type' => 'challenge',
            'title' => (string) $this->title,
            'description' => (string) $this->description,
            'url' => route('challenge_page', $this->id),
            'author' => $this->author($this->user),
            'media' => app(HomeMediaResolver::class)->contest($this->resource),
            'status' => [
                'title' => $statusTitle,
                'finished' => (bool) $this->finished,
                'started' => (bool) $this->started,
            ],
            'reward_amount' => (int) ($this->reward_amount ?? 0),
            'participation_cost' => (int) ($this->cost ?? 0),
            'participants' => [
                'current' => $currentParticipants,
                'limit' => $this->participants_count !== null ? (int) $this->participants_count : null,
                'minimum' => $minimumParticipants,
            ],
            'deadline' => $this->isoDate($this->date_to ?: $this->finish),
            'stats' => [
                'views' => $this->count($this->resource, 'views_count', 'views'),
                'likes' => $this->count($this->resource, 'likes_count', 'likes'),
                'comments' => $this->count($this->resource, 'comments_count', 'comments'),
            ],
        ];
    }
}
