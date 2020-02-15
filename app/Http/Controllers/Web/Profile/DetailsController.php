<?php

namespace Blegrator\Http\Controllers\Web\Profile;

use Blegrator\Events\User\UpdatedProfileDetails;
use Blegrator\Http\Controllers\Controller;
use Blegrator\Http\Requests\User\UpdateProfileDetailsRequest;
use Blegrator\Repositories\User\UserRepository;

/**
 * Class DetailsController
 * @package Blegrator\Http\Controllers
 */
class DetailsController extends Controller
{
    /**
     * @var UserRepository
     */
    private $users;

    /**
     * DetailsController constructor.
     * @param UserRepository $users
     */
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * Update profile details.
     *
     * @param UpdateProfileDetailsRequest $request
     * @return mixed
     */
    public function update(UpdateProfileDetailsRequest $request)
    {
        $this->users->update(auth()->id(), $request->except('role_id', 'status'));

        event(new UpdatedProfileDetails);

        return redirect()->back()
            ->withSuccess(__('Profile updated successfully.'));
    }
}
