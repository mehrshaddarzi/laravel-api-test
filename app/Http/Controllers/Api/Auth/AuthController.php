<?php

namespace Blegrator\Http\Controllers\Api\Auth;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Blegrator\Events\User\LoggedIn;
use Blegrator\Events\User\LoggedOut;
use Blegrator\Http\Controllers\Api\ApiController;
use Blegrator\Http\Requests\Auth\LoginRequest;

/**
 * Class LoginController
 * @package Blegrator\Http\Controllers\Api\Auth
 */
class AuthController extends ApiController
{
    public function __construct()
    {
        $this->middleware('guest')->only('login');
        $this->middleware('auth')->only('logout');
    }

    /**
     * Attempt to log the user in and generate unique
     * JWT token on successful authentication.
     *
     * @param LoginRequest $request
     * @return JsonResponse|Response
     * @throws BindingResolutionException
     */
    public function login(LoginRequest $request)
    {
        $credentials = $request->getCredentials();

        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return $this->errorUnauthorized('Invalid credentials.');
            }
        } catch (JWTException $e) {
            return $this->errorInternalError('Could not create token.');
        }

        $user = auth()->user();

        if ($user->isBanned()) {
            $this->invalidateToken($token);
            return $this->errorUnauthorized('Your account is banned by administrators.');
        }

        if ($user->isUnconfirmed()) {
            $this->invalidateToken($token);
            return $this->errorUnauthorized('Please confirm your email address first.');
        }

        event(new LoggedIn);

        return $this->respondWithArray(compact('token'));
    }

    private function invalidateToken($token)
    {
        JWTAuth::setToken($token);
        JWTAuth::invalidate();
    }

    /**
     * Logout user and invalidate token.
     * @return JsonResponse
     */
    public function logout()
    {
        event(new LoggedOut);

        auth()->logout();

        return $this->respondWithSuccess();
    }
}
