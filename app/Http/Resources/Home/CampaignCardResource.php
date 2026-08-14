<?php

declare(strict_types=1);

namespace App\Http\Resources\Home;

use App\Services\Home\HomeMediaResolver;

final class CampaignCardResource extends HomeCardResource
{
    public function toArray($request): array
    {
        $attributes = $this->resource->getAttributes();
        $raised = (float) ($attributes['success_payments_sum_amount']
            ?? ($this->relationLoaded('success_payments') ? $this->success_payments->sum('amount') : 0));
        $goal = (float) ($this->goal ?? 0);

        return [
            'id' => (int) $this->id,
            'title' => (string) $this->title,
            'description' => (string) ($this->short_description ?? ''),
            'url' => route('campaign_single', $this->slug),
            'author' => $this->author($this->user),
            'media' => app(HomeMediaResolver::class)->campaign($this->resource),
            'category' => $this->get_category ? [
                'id' => (int) $this->get_category->id,
                'title' => $this->get_category->category_name,
                'slug' => $this->get_category->slug,
            ] : null,
            'funding' => [
                'goal' => $goal,
                'raised' => $raised,
                'progress' => $goal > 0 ? round(min(100, ($raised / $goal) * 100), 2) : 0,
                'sponsors' => $this->relationLoaded('success_payments') ? $this->success_payments->count() : 0,
            ],
            'created_at' => $this->isoDate($this->created_at),
        ];
    }
}
