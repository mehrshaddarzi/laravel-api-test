<?php

namespace Blegrator\Listeners\Users;

use Illuminate\Auth\Events\Verified;
use Blegrator\Repositories\User\UserRepository;
use Blegrator\Support\Enum\UserStatus;

class ActivateUser
{
    /**
     * @var UserRepository
     */
    private $users;

    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * Handle the event.
     *
     * @param Verified $event
     * @return void
     */
    public function handle(Verified $event)
    {
        $this->users->update($event->user->id, [
            'status' => UserStatus::ACTIVE
        ]);
    }
}
