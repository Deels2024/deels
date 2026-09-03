<?php

declare(strict_types=1);

namespace App\Http\Resources\Home;

use App\Models\Story;
use App\Services\Home\HomeMediaResolver;

final class BattleCardResource extends HomeCardResource
{
    public function toArray($request): array
    {
        $media = app(HomeMediaResolver::class);
        $mainStory = $this->relationLoaded('getMainStory') ? $this->getRelation('getMainStory') : null;
        $stories = $this->relationLoaded('stories') ? $this->getRelation('stories') : collect();
        $opponentStory = $stories->firstWhere('user_id', $this->called_user_id) ?: $stories->first();
        $calledUser = $opponentStory?->user ?: $this->calledUser;

        return [
            'id' => (int) $this->id,
            'type' => 'battle',
            'title' => (string) $this->title,
            'description' => (string) $this->description,
            'url' => route('deels.public.battles.show', ['id' => $this->id]),
            'reward_amount' => (int) ($this->reward_amount ?? 0),
            'deadline' => $this->isoDate($this->date_to ?: $this->finish),
            'opponents' => [
                [
                    'side' => 'creator',
                    'author' => $this->author($this->user),
                    'media' => $mainStory instanceof Story
                        ? $media->story($mainStory)
                        : $media->contest($this->resource, '9:16'),
                ],
                [
                    'side' => 'opponent',
                    'author' => $this->author($calledUser),
                    'media' => $opponentStory instanceof Story ? $media->story($opponentStory) : null,
                ],
            ],
            'participants' => [
                'current' => $this->count($this->resource, 'stories_count', 'stories'),
                'limit' => $this->participants_count !== null ? (int) $this->participants_count : 2,
            ],
            'stats' => [
                'views' => $this->count($this->resource, 'views_count', 'views'),
                'likes' => $this->count($this->resource, 'likes_count', 'likes'),
                'comments' => $this->count($this->resource, 'comments_count', 'comments'),
            ],
        ];
    }
}
