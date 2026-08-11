<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\AppHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

class View extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::created(function (View $view) {
            $helper = new AppHelper();
            $helper->save_action('view', $view->user_id, $view->story);
        });

        static::addGlobalScope('with_users', function (Builder $builder) {
            $builder->with('user');
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class, 'story_id')->withoutGlobalScopes();
    }
}
