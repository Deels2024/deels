<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    protected $namespace = 'App\Http\Controllers';

    public function boot(): void
    {
        RateLimiter::for('registrations', function (Request $request) {
            return Limit::perDay(10)
                ->by($request->ip())
                ->response(function (Request $request, array $headers) {
                    $message = 'Слишком много регистраций с вашего IP. Попробуйте позже';

                    if ($request->is('api/*') || $request->expectsJson()) {
                        return response()->json([
                            'success' => false,
                            'error' => $message,
                        ], 429, $headers);
                    }

                    return redirect()->back()
                        ->withInput($request->except('password', 'password_confirmation'))
                        ->withErrors(['registration' => $message])
                        ->withHeaders($headers);
                });
        });

        parent::boot();

        Route::namespace($this->namespace)
            ->group(base_path('routes/bot.php'));
    }

    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapDeelsCompatibilityRoutes();
        $this->mapDeelsBattleCompatibilityRoutes();
        $this->mapDeelsPublicRoutes();
        $this->mapWebRoutes();
    }

    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    protected function mapDeelsCompatibilityRoutes(): void
    {
        Route::prefix('api')
             ->middleware('api')
             ->group(base_path('routes/deels_compat.php'));
    }

    /** Canonical battle routes used by the new Deels facade. */
    protected function mapDeelsBattleCompatibilityRoutes(): void
    {
        Route::prefix('api')
             ->middleware('api')
             ->group(base_path('routes/deels_battle_compat.php'));
    }

    /** Clean, indexable public URLs for the new facade. */
    protected function mapDeelsPublicRoutes(): void
    {
        Route::middleware('web')
             ->group(base_path('routes/deels_public.php'));
    }
}
