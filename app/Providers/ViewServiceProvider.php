<?php

declare(strict_types=1);

namespace App\Providers;

use App\Models\Comment;
use App\Models\Payment;
use Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class ViewServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        View::composer('admin.menu', function ($view): void {
            $campaign_ids = Auth::user()->my_campaigns()->pluck('id')->toArray();

            $comments = Comment::query()
                               ->whereIn('campaign_id', $campaign_ids)
                               ->where('created_at', '>', Auth::user()->last_active ?? 0)
                               ->count();

            $payments = Payment::success()
                               ->whereIn('campaign_id', $campaign_ids)
                               ->where('created_at', '>', Auth::user()->last_active ?? 0)
                               ->count();

            $view->with([
                'commentsCount' => $comments,
                'paymentsCount' => $payments,
            ]);
        });
    }

    public function register(): void
    {
    }
}
