<?php

declare(strict_types=1);

namespace App\Models;

class PendingReferrals extends Model
{
    protected $guarded = [];

    protected $casts = ['data' => 'array'];
}
