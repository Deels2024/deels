<?php

declare(strict_types=1);

namespace App\Models;

use App\Helpers\AppHelper;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Dislikes extends Model
{
    protected $guarded = [];

    protected static function boot()
    {
        parent::boot();

        static::created(function (Dislikes $like) {
            $helper = new AppHelper();
            $helper->save_action('dislike', $like->user_id, $like->campaign ?? $like->story ?? $like->comment);
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

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }

    public function comment(): BelongsTo
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

}
