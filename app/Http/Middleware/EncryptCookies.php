<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as BaseEncrypter;

class EncryptCookies extends BaseEncrypter
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array
     */
    protected $except = [
        'refCode',
        'web_app',
        'lastNews',
        'payed_campaign',
        'promo_block',
        'mobile_app',
        'challenge_mobile_app',
        'twitch_block',
    ];
}
