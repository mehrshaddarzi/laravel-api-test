<?php

namespace Blegrator\Http\Controllers\Api\Profile;

use Blegrator\Events\User\UpdatedProfileDetails;
use Blegrator\Http\Controllers\Api\ApiController;
use Blegrator\Http\Requests\User\UpdateProfileDetailsRequest;
use Blegrator\Http\Requests\User\UpdateProfileLoginDetailsRequest;
use Blegrator\Repositories\User\UserRepository;
use Blegrator\Transformers\UserTransformer;

/**
 * Class DetailsController
 * @package Blegrator\Http\Controllers\Api\Profile
 */
class AuthDetailsController extends ApiController
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Updates user profile details.
     * @param UpdateProfileLoginDetailsRequest $request
     * @param UserRepository $users
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateProfileLoginDetailsRequest $request, UserRepository $users)
    {
        $user = $request->user();

        $data = $request->only(['email', 'username', 'password']);

        $user = $users->update($user->id, $data);

        return $this->respondWithItem($user, new UserTransformer);
    }
}
