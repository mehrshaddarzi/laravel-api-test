<?php

namespace Blegrator\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Blegrator\Events\User\Banned;
use Blegrator\Events\User\LoggedIn;
use Blegrator\Listeners\Users\ActivateUser;
use Blegrator\Listeners\Users\InvalidateSessionsAndTokens;
use Blegrator\Listeners\Login\UpdateLastLoginTimestamp;
use Blegrator\Listeners\Registration\SendSignUpNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
            SendSignUpNotification::class,
        ],
        LoggedIn::class => [
            UpdateLastLoginTimestamp::class
        ],
        Banned::class => [
            InvalidateSessionsAndTokens::class
        ],
        Verified::class => [
            ActivateUser::class
        ]
    ];

    /**
     * The subscriber classes to register.
     *
     * @var array
     */
    protected $subscribe = [
        //
    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
