<?php

use Blegrator\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Role::create([
            'name' => 'Admin',
            'display_name' => 'Admin',
            'description' => 'System administrator.',
            'removable' => false
        ]);

        Role::create([
            'name' => 'Customer',
            'display_name' => 'Customer',
            'description' => 'Customer system user.',
            'removable' => false
        ]);

        Role::create([
            'name' => 'Employee.Salesman',
            'display_name' => 'Employee Salesman',
            'description' => 'Employee Salesman system user.',
            'removable' => false
        ]);

        Role::create([
            'name' => 'Employee.Technician',
            'display_name' => 'Employee Technician',
            'description' => 'Employee Technician system user.',
            'removable' => false
        ]);

        Role::create([
            'name' => 'Employee.Manager',
            'display_name' => 'Employee Manager',
            'description' => 'Employee Manager system user.',
            'removable' => false
        ]);

        Role::create([
            'name' => 'Company.Reseller',
            'display_name' => 'Company Reseller',
            'description' => 'Company Reseller system user.',
            'removable' => false
        ]);

        Role::create([
            'name' => 'Company.ServiceProvider',
            'display_name' => 'Company ServiceProvider',
            'description' => 'Company ServiceProvider system user.',
            'removable' => false
        ]);

        Role::create([
            'name' => 'Company.Both',
            'display_name' => 'Company Both',
            'description' => 'Company Both system user.',
            'removable' => false
        ]);
    }
}
