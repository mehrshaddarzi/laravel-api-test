<?php

namespace Blegrator\Http\Controllers\Api\Users;

use Authy;
use Illuminate\Http\Request;
use Blegrator\Events\User\TwoFactorDisabledByAdmin;
use Blegrator\Events\User\TwoFactorEnabledByAdmin;
use Blegrator\Http\Controllers\Api\ApiController;
use Blegrator\Http\Requests\TwoFactor\EnableTwoFactorRequest;
use Blegrator\Http\Requests\TwoFactor\VerifyTwoFactorTokenRequest;
use Blegrator\Transformers\UserTransformer;
use Blegrator\User;

/**
 * Class TwoFactorController
 * @package Blegrator\Http\Controllers\Api\Users
 */
class TwoFactorController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:users.manage');
    }

    /**
     * Enable 2FA for specified user.
     * @param User $user
     * @param EnableTwoFactorRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(User $user, EnableTwoFactorRequest $request)
    {
        if (Authy::isEnabled($user)) {
            return $this->setStatusCode(422)
                ->respondWithError("2FA is already enabled for this user.");
        }

        $user->setAuthPhoneInformation($request->country_code, $request->phone_number);

        Authy::register($user);

        $user->save();

        Authy::sendTwoFactorVerificationToken($user);

        return $this->respondWithArray([
            'message' => 'Verification token sent.'
        ]);
    }

    /**
     * Verify provided 2FA token.
     *
     * @param VerifyTwoFactorTokenRequest $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function verify(VerifyTwoFactorTokenRequest $request, User $user)
    {
        if (! Authy::tokenIsValid($user, $request->token)) {
            return $this->setStatusCode(422)
                ->respondWithError("Invalid 2FA token.");
        }

        $user->setTwoFactorAuthProviderOptions(array_merge(
            $user->getTwoFactorAuthProviderOptions(),
            ['enabled' => true]
        ));

        $user->save();

        event(new TwoFactorEnabledByAdmin($user));

        return $this->respondWithItem($user, new UserTransformer);
    }

    /**
     * Disable 2FA for specified user.
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(User $user)
    {
        if (! Authy::isEnabled($user)) {
            return $this->setStatusCode(422)
                ->respondWithError("2FA is not enabled for this user.");
        }

        Authy::delete($user);

        $user->save();

        event(new TwoFactorDisabledByAdmin($user));

        return $this->respondWithItem($user, new UserTransformer);
    }
}
