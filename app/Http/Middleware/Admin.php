<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('error', trans('app.unauthorized_access'));
        }

        $user = Auth::user();

        if (!$user->is_admin() && !$user->is_campaign_admin() && !$user->is_comment_admin()) {
            return redirect(route('dashboard'))->with('error', trans('app.access_restricted'));
        }

        return $next($request);
    }
}
