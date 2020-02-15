<?php

namespace Blegrator\Listeners\Login;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Blegrator\Events\User\LoggedIn;
use Blegrator\Repositories\User\UserRepository;

class UpdateLastLoginTimestamp
{
    /**
     * @var UserRepository
     */
    private $users;
    /**
     * @var Guard
     */
    private $guard;

    public function __construct(UserRepository $users, Guard $guard)
    {
        $this->users = $users;
        $this->guard = $guard;
    }

    /**
     * Handle the event.
     *
     * @param LoggedIn $event
     * @return void
     */
    public function handle(LoggedIn $event)
    {
        $this->users->update(
            $this->guard->id(),
            ['last_login' => Carbon::now()]
        );
    }
}
