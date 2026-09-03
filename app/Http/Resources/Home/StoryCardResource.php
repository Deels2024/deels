<?php

declare(strict_types=1);

namespace App\Http\Resources\Home;

use App\Services\Home\HomeMediaResolver;
use Illuminate\Support\Str;

final class StoryCardResource extends HomeCardResource
{
    public function toArray($request): array
    {
        $description = trim(strip_tags((string) $this->description));

        return [
            'id' => (int) $this->id,
            'title' => Str::limit($description, 80),
            'description' => $description,
            'url' => route('deels.public.stories.show', ['id' => $this->id]),
            'preview_url' => route('stories.preview', ['id' => $this->id]),
            'author' => $this->author($this->user),
            'media' => app(HomeMediaResolver::class)->story($this->resource),
            'access' => [
                'paid' => (bool) $this->paid,
                'amount' => (int) $this->amount,
                'viewed' => (bool) $this->getAttribute('is_viewed'),
            ],
            'stats' => [
                'views' => $this->count($this->resource, 'views_count', 'views'),
                'likes' => $this->count($this->resource, 'likes_count', 'likes'),
                'comments' => $this->count($this->resource, 'comments_count', 'comments'),
            ],
            'created_at' => $this->isoDate($this->created_at),
        ];
    }
}
