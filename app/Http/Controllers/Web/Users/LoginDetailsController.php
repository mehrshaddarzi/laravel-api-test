<?php

namespace Blegrator\Http\Controllers\Web\Users;

use Blegrator\Events\User\UpdatedByAdmin;
use Blegrator\Http\Controllers\Controller;
use Blegrator\Http\Requests\User\UpdateLoginDetailsRequest;
use Blegrator\Repositories\User\UserRepository;
use Blegrator\User;

/**
 * Class UserDetailsController
 * @package Blegrator\Http\Controllers\Users
 */
class LoginDetailsController extends Controller
{
    /**
     * @var UserRepository
     */
    private $users;

    /**
     * UsersController constructor.
     * @param UserRepository $users
     */
    public function __construct(UserRepository $users)
    {
        $this->users = $users;
    }

    /**
     * Update user's login details.
     *
     * @param User $user
     * @param UpdateLoginDetailsRequest $request
     * @return mixed
     */
    public function update(User $user, UpdateLoginDetailsRequest $request)
    {
        $data = $request->all();

        if (! $data['password']) {
            unset($data['password']);
            unset($data['password_confirmation']);
        }

        $this->users->update($user->id, $data);

        event(new UpdatedByAdmin($user));

        return redirect()->route('users.edit', $user->id)
            ->withSuccess(__('Login details updated successfully.'));
    }
}
