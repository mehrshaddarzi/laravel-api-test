<?php

namespace Blegrator\Providers;

use Carbon\Carbon;
use Blegrator\Repositories\Country\CountryRepository;
use Blegrator\Repositories\Country\EloquentCountry;
use Blegrator\Repositories\Permission\EloquentPermission;
use Blegrator\Repositories\Permission\PermissionRepository;
use Blegrator\Repositories\Role\EloquentRole;
use Blegrator\Repositories\Role\RoleRepository;
use Blegrator\Repositories\Session\DbSession;
use Blegrator\Repositories\Session\SessionRepository;
use Blegrator\Repositories\User\EloquentUser;
use Blegrator\Repositories\User\UserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Carbon::setLocale(config('app.locale'));
        config(['app.name' => setting('app_name')]);
        \Illuminate\Database\Schema\Builder::defaultStringLength(191);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(UserRepository::class, EloquentUser::class);
        $this->app->singleton(RoleRepository::class, EloquentRole::class);
        $this->app->singleton(PermissionRepository::class, EloquentPermission::class);
        $this->app->singleton(SessionRepository::class, DbSession::class);
        $this->app->singleton(CountryRepository::class, EloquentCountry::class);

        if ($this->app->environment('local')) {
            $this->app->register(\Barryvdh\LaravelIdeHelper\IdeHelperServiceProvider::class);
            $this->app->register(\Barryvdh\Debugbar\ServiceProvider::class);
        }
    }
}
