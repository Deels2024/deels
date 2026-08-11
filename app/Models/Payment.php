<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $guarded = [];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function comment(): HasOne
    {
        return $this->hasOne(PaymentComment::class);
    }

    public function reward(): BelongsTo
    {
        return $this->belongsTo(Reward::class);
    }

    public function scopeSuccess($query)
    {
        return $query->whereStatus('success');
    }

    public function scopePending($query)
    {
        return $query->whereStatus('pending');
    }

    public function thanks(): HasOne
    {
        return $this->hasOne(Thanks::class);
    }

    public function user(): BelongsTo
    {
        return $this
            ->belongsTo(User::class);
//            ->where('email', '!=', 'info@deels.ru')
//            ->where('user_type', 'user')
    }
}
