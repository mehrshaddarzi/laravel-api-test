<?php

namespace Blegrator\Listeners\Users;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Blegrator\Events\User\Banned;
use Blegrator\Events\User\LoggedIn;
use Blegrator\Repositories\Session\SessionRepository;
use Blegrator\Repositories\User\UserRepository;
use Blegrator\Services\Auth\Api\Token;

class InvalidateSessionsAndTokens
{
    /**
     * @var SessionRepository
     */
    private $sessions;

    public function __construct(SessionRepository $sessions)
    {
        $this->sessions = $sessions;
    }

    /**
     * Handle the event.
     *
     * @param Banned $event
     * @return void
     */
    public function handle(Banned $event)
    {
        $user = $event->getBannedUser();

        $this->sessions->invalidateAllSessionsForUser($user->id);

        Token::where('user_id', $user->id)->delete();
    }
}
