<?php

declare(strict_types=1);

namespace App\Models;

use Bavix\Wallet\Interfaces\Wallet;
use Bavix\Wallet\Interfaces\WalletFloat;
use Bavix\Wallet\Traits\HasWalletFloat;
use Bavix\Wallet\Traits\HasWallets;

class ProjectAccount extends Model implements Wallet, WalletFloat
{
    use HasWalletFloat;
    use HasWallets;

    protected $guarded = [];

    protected $casts = [
        'meta' => 'array',
    ];
}
