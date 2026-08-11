<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class StoreReferralCode
{
    private const COOKIE_NAME = 'refCode';
    private const COOKIE_LIFETIME_MINUTES = 60 * 24 * 30;

    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);
        $referralCode = $request->query('ref');

        if (!$referralCode || !User::query()->where('referral_code', $referralCode)->exists()) {
            return $response;
        }

        if ($request->user() && $request->user()->referral_code === $referralCode) {
            return $response;
        }

        return $response->withCookie(
            Cookie::make(self::COOKIE_NAME, $referralCode, self::COOKIE_LIFETIME_MINUTES, '/', null, null, false)
        );
    }
}
