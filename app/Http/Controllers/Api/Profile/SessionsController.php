<?php

namespace Blegrator\Http\Controllers\Api\Profile;

use Blegrator\Http\Controllers\Api\ApiController;
use Blegrator\Repositories\Session\SessionRepository;
use Blegrator\Transformers\SessionTransformer;

/**
 * Class DetailsController
 * @package Blegrator\Http\Controllers\Api\Profile
 */
class SessionsController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('session.database');
    }

    /**
     * Handle user details request.
     * @param SessionRepository $sessions
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(SessionRepository $sessions)
    {
        $sessions = $sessions->getUserSessions(auth()->id());

        return $this->respondWithCollection(
            $sessions,
            new SessionTransformer
        );
    }
}
