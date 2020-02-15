<?php

namespace Blegrator\Http;

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
        \Blegrator\Http\Middleware\VerifyInstallation::class,
        \Illuminate\Foundation\Http\Middleware\CheckForMaintenanceMode::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \Blegrator\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
        \Blegrator\Http\Middleware\TrustProxies::class,
    ];
    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
            \Blegrator\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            // \Illuminate\Session\Middleware\AuthenticateSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Blegrator\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
        'api' => [
            \Blegrator\Http\Middleware\UseApiGuard::class,
            'throttle:60,1',
            'bindings'
        ],
    ];

    /**
     * The application's route middleware.
     *
     * @var array
     */
    protected $routeMiddleware = [
        'auth' => \Blegrator\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'bindings' => \Illuminate\Routing\Middleware\SubstituteBindings::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \Blegrator\Http\Middleware\RedirectIfAuthenticated::class,
        'signed' => \Illuminate\Routing\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
        'registration' => \Blegrator\Http\Middleware\RegistrationEnabled::class,
        'social.login' => \Blegrator\Http\Middleware\SocialLogin::class,
        'role' => \Blegrator\Http\Middleware\CheckRole::class,
        'permission' => \Blegrator\Http\Middleware\CheckPermissions::class,
        'session.database' => \Blegrator\Http\Middleware\DatabaseSession::class,
        'two-factor' => \Blegrator\Http\Middleware\TwoFactorEnabled::class,
        'verify-2fa-phone' => \Blegrator\Http\Middleware\VerifyTwoFactorPhone::class,
        'password-reset' => \Blegrator\Http\Middleware\PasswordResetEnabled::class,
    ];

    /**
     * The priority-sorted list of middleware.
     *
     * This forces non-global middleware to always be in the given order.
     *
     * @var array
     */
    protected $middlewarePriority = [
        \Illuminate\Session\Middleware\StartSession::class,
        \Illuminate\View\Middleware\ShareErrorsFromSession::class,
        \Blegrator\Http\Middleware\Authenticate::class,
        \Illuminate\Session\Middleware\AuthenticateSession::class,
        \Illuminate\Routing\Middleware\SubstituteBindings::class,
        \Illuminate\Auth\Middleware\Authorize::class,
    ];
}
