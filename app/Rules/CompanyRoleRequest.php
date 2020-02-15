<?php

namespace Blegrator\Rules;

use Blegrator\Role;
use Illuminate\Contracts\Validation\Rule;

class CompanyRoleRequest implements Rule
{

    public $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Get User role
        $user_role = Role::getRoleNameByID($this->user->role_id);

        // Request Only From Admin and Company
        if (!in_array($user_role, array_merge(['Admin'], Role::companyRoleList()))) {
            return false;
        }

        // Company Only Change Own service
        if (in_array($user_role, Role::companyRoleList()) and $value != $this->user->id) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'User role is not access permission for this request.';
    }
}
