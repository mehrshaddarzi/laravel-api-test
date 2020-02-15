<?php

namespace Blegrator\Events\Permission;

use Blegrator\Permission;

abstract class PermissionEvent
{
    /**
     * @var Permission
     */
    protected $permission;

    public function __construct(Permission $permission)
    {
        $this->permission = $permission;
    }

    /**
     * @return Permission
     */
    public function getPermission()
    {
        return $this->permission;
    }
}
