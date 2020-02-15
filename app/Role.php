<?php

namespace Blegrator;

use Illuminate\Database\Eloquent\Model;
use Blegrator\Support\Authorization\AuthorizationRoleTrait;

class Role extends Model
{
    use AuthorizationRoleTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'roles';

    protected $casts = [
        'removable' => 'boolean'
    ];

    protected $fillable = ['name', 'display_name', 'description'];

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public static function companyRoleList()
    {
        return ['Company.ServiceProvider', 'Company.Both', 'Company.Reseller'];
    }

    public static function employeeRoleList()
    {
        return ['Employee.Salesman', 'Employee.Technician', 'Employee.Manager'];
    }

    public static function getRoleNameByID($role_id)
    {
        return \Blegrator\Role::where('id', '=', $role_id)->select(['name'])->get()->pluck('name')->first();
    }
}
