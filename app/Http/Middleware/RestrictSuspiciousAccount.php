<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\SuspiciousAccountService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RestrictSuspiciousAccount
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user() ?: Auth::user();

        if (! $user || ! $user->is_suspicious) {
            return $next($request);
        }

        $restriction = app(SuspiciousAccountService::class)->restriction($user);
        if ($restriction === null) {
            return $next($request);
        }

        return response()->json($restriction, 403);
    }
}
