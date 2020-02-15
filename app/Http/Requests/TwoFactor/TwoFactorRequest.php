<?php

namespace Blegrator\Http\Requests\TwoFactor;

use Blegrator\Http\Requests\Request;
use Blegrator\Repositories\User\UserRepository;

abstract class TwoFactorRequest extends Request
{
    /**
     * Authorize the request.
     *
     * @return bool
     */
    public function authorize()
    {
        if ($userId = $this->get('user')) {
            // Only users with "users.manage" permission can enable 2FA for other users.
            return $this->user()->hasPermission('users.manage') || $this->user()->id == $userId;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [];
    }

    /**
     * Get the user for which we should enable the 2FA.
     *
     * @return mixed
     */
    public function theUser()
    {
        if ($userId = $this->get('user')) {
            return app(UserRepository::class)->find($userId);
        }

        return $this->user();
    }
}
