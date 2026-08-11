<?php

declare(strict_types=1);

namespace App\Http;

use App\Http\Middleware\Activation;
use App\Http\Middleware\Admin;
use App\Http\Middleware\EncryptCookies;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\StoreReferralCode;
use App\Http\Middleware\StoreWebAppCookie;
use App\Http\Middleware\TrimStrings;
use App\Http\Middleware\UserActive;
use App\Http\Middleware\VerifyCsrfToken;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        CheckForMaintenanceMode::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        StartSession::class,
        ShareErrorsFromSession::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StoreReferralCode::class,
            StoreWebAppCookie::class,
//            StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
//            ShareErrorsFromSession::class,
//            VerifyCsrfToken::class,
            SubstituteBindings::class,
            UserActive::class,
//            Activation::class
        ],

        'api' => [
//            'throttle:6000,1',
//            'bindings',
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'bindings' => SubstituteBindings::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'throttle' => ThrottleRequests::class,
        'admin' => Admin::class,
        'update.user.data' => \App\Http\Middleware\UpdateUserData::class,
        'suspicious.restricted' => \App\Http\Middleware\RestrictSuspiciousAccount::class,
        'action.limit.message' => \App\Http\Middleware\LimitMessageActions::class,
        'action.limit.like' => \App\Http\Middleware\LimitLikeActions::class,
    ];
}
