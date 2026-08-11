<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\UserActivityService;
use Auth;
use Carbon\Carbon;
use Closure;

class UserActive
{
    public function __construct(private UserActivityService $userActivityService)
    {
    }

    /**
     * @param $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($user = Auth::user()) {
            $this->userActivityService->updateWebActivity($user, $request);

            if ($request->routeIs('banned')) {
                return $next($request);
            }
            // Проверяем наличие поля banned_till и что срок бана еще не истек
            if ($user->banned_till && Carbon::now()->lt($user->banned_till)) {
                return redirect()->route('banned'); // Перенаправляем на страницу banned
            }
        }



        return $next($request);
    }

}
