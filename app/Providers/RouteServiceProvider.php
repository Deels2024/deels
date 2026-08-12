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
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     */
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

    /**
     * Define the routes for the application.
     */
    public function map(): void
    {
        $this->mapApiRoutes();
        $this->mapDeelsCompatibilityRoutes();
        $this->mapWebRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     */
    protected function mapWebRoutes(): void
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     */
    protected function mapApiRoutes(): void
    {
        Route::prefix('api')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    /**
     * REST aliases/adapters used by the new Deels design.
     */
    protected function mapDeelsCompatibilityRoutes(): void
    {
        Route::prefix('api')
             ->middleware('api')
             ->group(base_path('routes/deels_compat.php'));
    }
}
