<?php

namespace Blegrator\Transformers;

use League\Fractal\TransformerAbstract;
use Blegrator\Repositories\Country\CountryRepository;
use Blegrator\Repositories\Role\RoleRepository;
use Blegrator\User;

class UserTransformer extends TransformerAbstract
{
    protected $availableIncludes = ['role', 'country'];

    public function transform(User $user)
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->present()->avatar,
            'address' => $user->address,
            'physical_address' => $user->physical_address,
            'website' => $user->website,
            'country_id' => $user->country_id ? (int)$user->country_id : null,
            'city' => $user->city,
            'street' => $user->street,
            'zipcode' => $user->zipcode,
            'role_id' => (int)$user->role_id,
            'parent_id' => (int)$user->parent_id,
            'company_name' => $user->company_name,
            'company_registration_number' => $user->company_registration_number,
            'status' => $user->status,
            'birthday' => $user->birthday ? $user->birthday->format('Y-m-d') : null,
            'last_login' => (string)$user->last_login,
            'two_factor_country_code' => (int)$user->two_factor_country_code,
            'two_factor_phone' => (string)$user->two_factor_phone,
            'two_factor_options' => json_decode($user->two_factor_options, true),
            'email_verified_at' => $user->email_verified_at ? (string)$user->email_verified_at : null,
            'created_at' => (string)$user->created_at,
            'updated_at' => (string)$user->updated_at
        ];
    }

    public function includeRole(User $user)
    {
        if (!auth('api')->user()->hasPermission('roles.manage')) {
            return null;
        }

        return $this->item($user->role, new RoleTransformer);
    }

    public function includeCountry(User $user)
    {
        return $user->country
            ? $this->item($user->country, new CountryTransformer)
            : null;
    }
}
