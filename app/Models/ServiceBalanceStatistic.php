<?php

declare(strict_types=1);

namespace App\Models;

class ServiceBalanceStatistic extends Model
{
    protected $guarded = [];

    protected $casts = [
        'ucaller_balance' => 'float',
        'sms_balance' => 'float',
        'proxy_balance' => 'float',
        'proxies' => 'array',
        'errors' => 'array',
        'checked_at' => 'datetime',
    ];
}
