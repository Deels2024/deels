<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Activation
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse) $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if ($user = Auth::user()) {
            if($request->path() == 'logout') {
                return $next($request);
            }
            $is_activated = Auth::user()->is_activated;
            if ($user->phone && !$is_activated && $request->path() != 'logout' && !in_array(\Request::route()->getName(), ['activation','activation_verify', 'logout'])) {
                return redirect(route('activation'));
            }

            // Исключаем маршруты onboarding и logout из проверки
            if (!$user->is_onboarding && !$request->routeIs('activation', 'activation_verify', 'onboarding', 'logout', 'onboarding_finish')) {
//                return redirect()->route('onboarding');
            }
        }
        return $next($request);
    }
}
