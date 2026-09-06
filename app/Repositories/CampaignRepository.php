<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Campaign;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;

class CampaignRepository
{
    private Campaign $campaign;

    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function randomFullyDonatedCampaigns(int $count)
    {
        return
            Cache::remember(
                'fully_donated_campaigns',
                10,
                fn() => $this->campaign->fullyDonated()
                                       ->inRandomOrder()
                                       ->take($count + 5)
                                       ->when(request('asd'), fn(Builder $builder) => $builder->dd())
                                       ->get()
            );
    }

    public function fundedCampaigns(int $count): Collection
    {
        return Cache::remember(
            "funded_campaigns:{$count}",
            500,
            function() use ($count) {
                $campaignIds = Payment::select('campaign_id')
                                      ->where('payments.status', '=', 'success')
                                      ->groupBy('campaign_id')
                                      ->limit($count)
                                      ->get()
                                      ->pluck('campaign_id')
                                      ->toArray();

                return $this->campaignWithRelations()
                            ->whereIn('id', $campaignIds)
                            ->get();
            });
    }

    public function latestFundedCampaigns(int $count)
    {
        return Cache::remember(
            "latest_funded_campaigns:{$count}",
            500,
            function() use ($count) {
                $campaignIds = Payment::selectRaw('max(id),campaign_id')
                                      ->where('payments.status', '=', 'success')
                                      ->orderByDesc('max(id)')
                                      ->groupBy('campaign_id')
                                      ->limit($count)
                                      ->get()
                                      ->pluck('campaign_id')
                                      ->toArray();

                return $this->campaignWithRelations()
                            ->whereIn('id', $campaignIds)
                            ->get();
            });
    }

    public function newCampaigns(int $count, \Illuminate\Support\Collection $except)
    {
        return Cache::remember(
            'new_campaigns:' . $count . ':' . md5($except->sort()->implode(',')),
            500,
            fn() => $this->campaignWithRelations()
                         ->whereNotIn('id', $except)
                         ->orderBy('created_at', 'desc')
                         ->take($count)
                         ->get(['campaigns.*'])
        );
    }

    private function campaignWithRelations(): Builder
    {
        return $this
            ->campaign
            ->active()
            ->with('user', 'success_payments', 'feature_media', 'get_category', 'latestActiveStory.media', 'latestActiveStory.user')
            ->withSum('success_payments', 'amount');
    }
}
