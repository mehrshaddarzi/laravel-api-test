<?php

namespace Blegrator\Http\Controllers\Web\Auth;

use Illuminate\Auth\Events\Registered;
use Blegrator\Http\Controllers\Controller;
use Blegrator\Http\Requests\Auth\RegisterRequest;
use Blegrator\Repositories\Role\RoleRepository;
use Blegrator\Repositories\User\UserRepository;
use Blegrator\Support\Enum\UserStatus;

class RegisterController extends Controller
{
    /**
     * @var UserRepository
     */
    private $users;

    /**
     * Create a new authentication controller instance.
     * @param UserRepository $users
     */
    public function __construct(UserRepository $users)
    {
        $this->middleware('registration')->only('show', 'register');

        $this->users = $users;
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        return view('auth.register', [
            'socialProviders' => config('auth.social.providers')
        ]);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param RegisterRequest $request
     * @param RoleRepository $roles
     * @return \Illuminate\Http\Response
     */
    public function register(RegisterRequest $request, RoleRepository $roles)
    {
        $user = $this->users->create(
            array_merge($request->validFormData(), ['role_id' => $roles->findByName('Customer')->id])
        );

        event(new Registered($user));

        $message = setting('reg_email_confirmation')
            ? __('Your account is created successfully! Please confirm your email.')
            : __('Your account is created successfully!');

        \Auth::login($user);

        return redirect('/')->with('success', $message);
    }
}