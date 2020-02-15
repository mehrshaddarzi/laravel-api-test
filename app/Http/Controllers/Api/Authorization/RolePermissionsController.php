<?php

namespace Blegrator\Http\Controllers\Api\Authorization;

use Cache;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Blegrator\Events\Role\PermissionsUpdated;
use Blegrator\Http\Controllers\Api\ApiController;
use Blegrator\Http\Requests\Role\CreateRoleRequest;
use Blegrator\Http\Requests\Role\RemoveRoleRequest;
use Blegrator\Http\Requests\Role\UpdateRolePermissionsRequest;
use Blegrator\Http\Requests\Role\UpdateRoleRequest;
use Blegrator\Repositories\Role\RoleRepository;
use Blegrator\Repositories\User\UserRepository;
use Blegrator\Role;
use Blegrator\Transformers\PermissionTransformer;
use Blegrator\Transformers\RoleTransformer;

/**
 * Class RolePermissionsController
 * @package Blegrator\Http\Controllers\Api
 */
class RolePermissionsController extends ApiController
{
    /**
     * @var RoleRepository
     */
    private $roles;

    public function __construct(RoleRepository $roles)
    {
        $this->roles = $roles;
        $this->middleware('auth');
        $this->middleware('permission:permissions.manage');
    }

    public function show(Role $role)
    {
        return $this->respondWithCollection(
            $role->cachedPermissions(),
            new PermissionTransformer
        );
    }

    /**
     * Update specified role.
     * @param Role $role
     * @param UpdateRolePermissionsRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Role $role, UpdateRolePermissionsRequest $request)
    {
        $this->roles->updatePermissions(
            $role->id,
            $request->permissions
        );

        event(new PermissionsUpdated);

        return $this->respondWithCollection(
            $role->cachedPermissions(),
            new PermissionTransformer
        );
    }
}
