<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FriendSuggestion extends Model
{
    protected $guarded = [];

    protected $casts = [
        'followed_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    public function suggestedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suggested_user_id');
    }
}
