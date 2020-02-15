<?php

namespace Blegrator\Providers;

use Blegrator\Plugins\BlegratorServiceProvider as BaseBlegratorServiceProvider;
use Blegrator\Support\Plugins\Dashboard\Widgets\BannedUsers;
use Blegrator\Support\Plugins\Dashboard\Widgets\LatestRegistrations;
use Blegrator\Support\Plugins\Dashboard\Widgets\NewUsers;
use Blegrator\Support\Plugins\Dashboard\Widgets\RegistrationHistory;
use Blegrator\Support\Plugins\Dashboard\Widgets\TotalUsers;
use Blegrator\Support\Plugins\Dashboard\Widgets\UnconfirmedUsers;
use Blegrator\Support\Plugins\Dashboard\Widgets\UserActions;
use \Blegrator\UserActivity\Widgets\ActivityWidget;

class BlegratorServiceProvider extends BaseBlegratorServiceProvider
{
    /**
     * List of registered plugins.
     *
     * @return array
     */
    protected function plugins()
    {
        return [
            \Blegrator\Support\Plugins\Dashboard\Dashboard::class,
            \Blegrator\Support\Plugins\Users::class,
            \Blegrator\Support\Plugins\RolesAndPermissions::class,
            \Blegrator\Support\Plugins\Settings::class,
        ];
    }

    /**
     * Dashboard widgets.
     *
     * @return array
     */
    protected function widgets()
    {
        return [
            UserActions::class,
            TotalUsers::class,
            NewUsers::class,
            BannedUsers::class,
            UnconfirmedUsers::class,
            RegistrationHistory::class,
            LatestRegistrations::class,
        ];
    }
}
