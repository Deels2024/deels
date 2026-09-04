<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Campaign;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class CampaignFilterService
{
    private Campaign $campaign;

    public function __construct(Campaign $campaign)
    {
        $this->campaign = $campaign;
    }

    public function getFilteredCampaigns($data)
    {
        $data = optional($data);

        $userId = $data['user'];
        $categoryId = $data['category'];

        $campaignsBuilder = $this->campaign;

        if ('fully_donated' === $data['type']) {
            $campaignsBuilder = $campaignsBuilder->fullyDonated();
        }

        $campaignsBuilder = $campaignsBuilder->active()
            ->with('user', 'feature_media', 'get_category')
            ->withCount('success_payments')
            ->withSum('success_payments', 'amount')
            ->when($userId, fn($query) => $query->where('user_id', $userId))
            ->when($categoryId, fn($query) => $query->where('category_id', $categoryId));

        if ($data['days_left']) {
            $today = Carbon::now();
            $from = $today->addDays($data['days_left'][0])->format('Y-m-d');
            $to = $today->addDays($data['days_left'][1])->format('Y-m-d');

            $campaignsBuilder->where(
                static function (Builder $builder) use ($from, $to): void {
                    $builder
                        ->whereBetween('end_date', [$from, $to])
                        ->orWhereNull('end_date');
                }
            );
        }

        if ($data['category']) {
            $campaignsBuilder->where('category_id', $data['category']);
        }

        if ('big' === $data['type']) {
            $campaignsBuilder->where('goal', '>=', 100000)->orderBy('goal', 'DESC');
        }
        if ('new' === $data['type']) {
            $campaignsBuilder->orderBy('created_at', 'DESC');
        }
        if ('funded' === $data['type']) {
//            $campaignsBuilder->has('success_payments')->orderBy('success_payments_sum_amount', 'DESC');
            $campaignsBuilder->has('success_payments')
                ->whereRaw('campaigns.goal > (SELECT COALESCE(SUM(amount), 0) 
                                    FROM payments 
                                    WHERE payments.campaign_id = campaigns.id 
                                    AND payments.status = ? 
                                    AND amount > 0)', ['success']);
        }
        if ('all' === $data['type']) {
            $campaignsBuilder->inRandomOrder();
        }


        return $campaignsBuilder;
    }
}
