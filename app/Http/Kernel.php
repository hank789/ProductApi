<?php

namespace App\Http;

use App\Http\Middleware\GetUserFromToken;
use App\Http\Middleware\GetWeappUserFromToken;
use App\Http\Middleware\RefreshToken;
use App\Http\Middleware\ValidUserPhone;
use App\Http\Middleware\WeappUserAuth;
use Illuminate\Foundation\Http\Kernel as HttpKernel;

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
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'operator' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Session\Middleware\StartSession::class,
        ],
        'api' => [
            //'throttle:60,1',
            'bindings',
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
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.admin' => \App\Http\Middleware\AdminAuthenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'ban.user' => \App\Http\Middleware\BanUserCheck::class,
        'permission' => \Bican\Roles\Middleware\VerifyPermission::class,
        'installer' => \App\Http\Middleware\InstallerCheck::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'user.phone' => ValidUserPhone::class,

        'jwt.auth' => GetUserFromToken::class,
        'jwt.refresh' => RefreshToken::class,
        'jwt.weappAuth' => GetWeappUserFromToken::class,
        'jwt.weappConfig' => WeappUserAuth::class,
    ];
}
