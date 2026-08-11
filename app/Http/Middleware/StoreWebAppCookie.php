<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class StoreWebAppCookie
{
    private const COOKIE_NAME = 'web_app';
    private const COOKIE_LIFETIME_MINUTES = 60 * 24 * 30;

    public function handle(Request $request, Closure $next)
    {
        if ($request->query('web_app') !== '1') {
            return $next($request);
        }

        $request->cookies->set(self::COOKIE_NAME, '1');

        $response = $next($request);

        return $response->withCookie(
            Cookie::make(self::COOKIE_NAME, '1', self::COOKIE_LIFETIME_MINUTES, '/', null, null, false)
        );
    }
}
