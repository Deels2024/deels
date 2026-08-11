<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\MessageActionRateLimiter;
use App\Services\SuspiciousAccountService;
use Closure;
use Illuminate\Http\Request;

class LimitMessageActions
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user) {
            return $next($request);
        }

        $reason = app(MessageActionRateLimiter::class)->hit(
            $user,
            (string) $request->input('message', '')
        );

        if ($reason === null) {
            return $next($request);
        }

        $service = app(SuspiciousAccountService::class);
        $service->markSuspicious($user);
        $payload = $service->restriction($user->fresh());
        $payload['reason'] = $reason;

        return response()->json($payload, 403, [
            'Retry-After' => (string) ($payload['retry_after'] ?? 3600),
        ]);
    }
}
