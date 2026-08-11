<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContestReport extends Model
{
    public $guarded = [];

    protected $casts = [
        'value' => 'float',
        'period_started_at' => 'datetime',
        'period_ended_at' => 'datetime',
    ];

    public function story(): BelongsTo
    {
        return $this->belongsTo(Story::class)->withoutGlobalScopes();
    }
}
