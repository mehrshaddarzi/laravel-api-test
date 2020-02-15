<?php

namespace Blegrator\Http\Controllers\Api\Auth\Password;

use Blegrator\Events\User\RequestedPasswordResetEmail;
use Blegrator\Http\Controllers\Api\ApiController;
use Blegrator\Http\Requests\Auth\PasswordRemindRequest;
use Blegrator\Mail\ResetPassword;
use Blegrator\Repositories\User\UserRepository;
use Password;

class RemindController extends ApiController
{
    /**
     * Create a new password controller instance.
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Send a reset link to the given user.
     *
     * @param PasswordRemindRequest $request
     * @param UserRepository $users
     * @return \Illuminate\Http\Response
     */
    public function index(PasswordRemindRequest $request, UserRepository $users)
    {
        $user = $users->findByEmail($request->email);

        $token = Password::getRepository()->create($user);

        \Mail::to($user)->send(new ResetPassword($token));

        event(new RequestedPasswordResetEmail($user));

        return $this->respondWithSuccess();
    }
}
