<?php

use Blegrator\Role;
use Blegrator\Support\Enum\UserStatus;
use Blegrator\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create Admin User
        $admin = Role::where('name', 'Admin')->first();

        User::create([
            'first_name' => 'Sina',
            'last_name' => 'Ghazi',
            'email' => 'hi@sinaghazi.com',
            'username' => 'sghazi',
            'password' => 'admin2020',
            'avatar' => null,
            'country_id' => null,
            'role_id' => $admin->id,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        // Create Customer User
        $customer = Role::where('name', 'Customer')->first();

        User::create([
            'first_name' => 'John',
            'last_name' => 'Dee',
            'email' => 'hi@johndee.com',
            'username' => 'customer',
            'password' => 'customer',
            'avatar' => null,
            'country_id' => null,
            'role_id' => $customer->id,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);

        // Create Company User
        $customer = Role::where('name', 'Company.Both')->first();
        User::create([
            'first_name' => 'Company Name',
            'last_name' => '',
            'email' => 'company@johndee.com',
            'username' => 'company',
            'password' => 'company',
            'avatar' => null,
            'country_id' => null,
            'role_id' => $customer->id,
            'status' => UserStatus::ACTIVE,
            'email_verified_at' => now(),
        ]);
    }
}
