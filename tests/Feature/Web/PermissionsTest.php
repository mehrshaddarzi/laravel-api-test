<?php

namespace Tests\Feature\Http\Controllers\Web;

use Facades\Tests\Setup\RoleFactory;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Blegrator\Events\Permission\Created;
use Blegrator\Events\Permission\Deleted;
use Blegrator\Events\Permission\Updated;
use Blegrator\Events\Role\PermissionsUpdated;
use Blegrator\Permission;
use Blegrator\Role;

class PermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');

        $this->be(UserFactory::admin()->create());
    }

    /** @test */
    public function permissionsList()
    {
        $permission = factory(Permission::class)->create();

        $this->get('permissions')
            ->assertSee($permission->display_name);
    }

    /** @test */
    public function onlyUsersWithAppropriatePermissionsCanAccessThePermissionsListPage()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('permissions.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $this->actingAs($userA)->get('/permissions')->assertStatus(403);
        $this->actingAs($userB)->get('/permissions')->assertOk();
    }

    /** @test */
    public function permissionListWithRoles()
    {
        $permission = factory(Permission::class)->create();
        $role = Role::first();

        $role->permissions()->attach($permission->id);

        $this->get('permissions')
            ->assertSee($permission->display_name)
            ->assertSee("roles[$role->id][]");
    }

    /** @test */
    public function saveRolePermissions()
    {
        $this->withoutExceptionHandling();
        $this->expectsEvents(PermissionsUpdated::class);

        $permission = factory(Permission::class)->create();
        $role = factory(Role::class)->create();

        $this->post('permissions/save', [
            'roles' => [
                $role->id => [$permission->id]
            ]
        ]);

        $this->assertSessionHasSuccess('Permissions saved successfully.');

        $this->assertDatabaseHas('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $permission->id
        ]);
    }

    /** @test */
    public function onlyUsersWithAppropriatePermissionsCanSaveRolePermissions()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('permissions.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $permission = factory(Permission::class)->create();
        $role = factory(Role::class)->create();

        $data = [$role->id => [$permission->id]];

        $this->actingAs($userA)
            ->post('permissions/save', ['roles' => $data])
            ->assertStatus(403);

        $this->assertDatabaseMissing('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $permission->id
        ]);

        $this->actingAs($userB)
            ->post('permissions/save', ['roles' => $data]);

        $this->assertDatabaseHas('permission_role', [
            'role_id' => $role->id,
            'permission_id' => $permission->id
        ]);
    }

    /** @test */
    public function saveRolePermissionsIfNoPermissionIsSelectedForSpecificRole()
    {
        $this->expectsEvents(PermissionsUpdated::class);

        $role1 = factory(Role::class)->create();
        $permission1 = factory(Permission::class)->create();
        $role1->permissions()->attach($permission1->id);

        $role2 = factory(Role::class)->create();
        $permission2 = factory(Permission::class)->create();
        $role2->permissions()->attach($permission2->id);

        $this->post('/permissions/save', [
            'roles' => [
                $role1->id => [$permission1->id]
            ]
        ]);

        $this->assertSessionHasSuccess('Permissions saved successfully.');

        $this->assertDatabaseHas('permission_role', [
            'role_id' => $role1->id,
            'permission_id' => $permission1->id
        ]);

        $this->assertDatabaseMissing('permission_role', [
            'role_id' => $role2->id,
            'permission_id' => $permission2->id
        ]);
    }

    /** @test */
    public function createPermission()
    {
        $this->app->instance('middleware.disable', false);

        $this->expectsEvents(Created::class);

        $data = $this->validParams();

        $this->post('permissions', $data)
            ->assertRedirect('/permissions');

        $this->assertSessionHasSuccess('Permission created successfully.');
        $this->assertDatabaseHas('permissions', $data);
    }

    /** @test */
    public function onlyUsersWithAppropriatePermissionCanCreateNewPermissions()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('permissions.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $data = $this->validParams();

        $this->actingAs($userA)
            ->post('permissions', $data)
            ->assertStatus(403);

        $this->assertDatabaseMissing('permissions', $data);

        $this->actingAs($userB)
            ->post('permissions', $data)
            ->assertRedirect('/permissions');

        $this->assertDatabaseHas('permissions', $data);
    }

    /** @test */
    public function permissionNameMustHaveValidFormat()
    {
        $this->app->instance('middleware.disable', false);

        $data = $this->validParams();

        $response = $this->post('permissions', $this->validParams(['name' => 'invalid name']));
        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('permissions', $data);

        $response = $this->post('permissions', $this->validParams(['name' => 'invalid*name']));
        $response->assertSessionHasErrors('name');
        $this->assertDatabaseMissing('permissions', $data);
    }

    /** @test */
    public function updatePermission()
    {
        $this->withoutExceptionHandling();
        $this->expectsEvents(Updated::class);

        $permission = factory(Permission::class)->create();

        $this->get("permissions/{$permission->id}/edit")
            ->assertOk()
            ->assertSee($permission->id)
            ->assertSee($permission->name)
            ->assertSee($permission->display_name);

        $data = $this->validParams();

        $this->put("permissions/{$permission->id}", $data);

        $this->assertSessionHasSuccess('Permission updated successfully.');
        $this->assertDatabaseHas('permissions', $data + ['id' => $permission->id]);
    }

    /** @test */
    public function onlyUsersWithAppropriatePermissionCanUpdateExistingPermissions()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('permissions.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $permission = factory(Permission::class)->create();

        $data = $this->validParams();

        $this->actingAs($userA)
            ->put("permissions/{$permission->id}", $data)
            ->assertStatus(403);

        $this->assertEquals($permission->toArray(), $permission->fresh()->toArray());

        $this->actingAs($userB)
            ->put("permissions/{$permission->id}", $data)
            ->assertRedirect('/permissions');

        $permission->refresh();

        $this->assertEquals($permission->name, $data['name']);
        $this->assertEquals($permission->display_name, $data['display_name']);
        $this->assertEquals($permission->description, $data['description']);
    }

    /** @test */
    public function removableAttributeCannotBeChangedOnUpdate()
    {
        $permission = factory(Permission::class)->create(['removable' => false]);

        $data = $this->validParams(['removable' => true]);

        $this->put("permissions/{$permission->id}", $data);

        $permission->refresh();

        $this->assertEquals($permission->name, $data['name']);
        $this->assertEquals($permission->display_name, $data['display_name']);
        $this->assertEquals($permission->description, $data['description']);
        $this->assertFalse($permission->removable);
    }

    /** @test */
    public function permissionNameMustHaveValidFormatWhileUpdatingThePermission()
    {
        $permission = factory(Permission::class)->create(['name' => 'foo']);

        $response = $this->put('permissions/' . $permission->id, $this->validParams(['name' => 'invalid name']));
        $response->assertSessionHasErrors('name');
        $this->assertEquals('foo', $permission->fresh()->name);

        $response = $this->put('permissions/' . $permission->id, $this->validParams(['name' => 'invalid*name']));
        $response->assertSessionHasErrors('name');
        $this->assertEquals('foo', $permission->fresh()->name);
    }

    /** @test */
    public function deletePermission()
    {
        $this->expectsEvents(Deleted::class);

        $permission = factory(Permission::class)->create();

        $this->delete(route('permissions.destroy', $permission->id))
            ->assertRedirect('/permissions');

        $this->assertSessionHasSuccess('Permission deleted successfully.');
        $this->assertDatabaseMissing('permissions', ['id' => $permission->id]);
    }

    /** @test */
    public function onlyUsersWithAppropriatePermissionCanDeletePermissions()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('permissions.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $permission = factory(Permission::class)->create();

        $this->actingAs($userA)
            ->delete("permissions/{$permission->id}")
            ->assertStatus(403);

        $this->assertNotNull($permission->fresh());

        $this->actingAs($userB)
            ->delete("permissions/{$permission->id}")
            ->assertRedirect('/permissions');

        $this->assertNull($permission->fresh());
    }

    /** @test */
    public function nonRemovablePermissionsCannotBeRemoved()
    {
        $permission = factory(Permission::class)->create(['removable' => false]);

        $this->delete(route('permissions.destroy', $permission->id))
            ->assertStatus(404);

        $this->assertNotNull($permission->fresh());
    }

    private function validParams(array $override = [])
    {
        return array_merge([
            'name' => 'foo_permission',
            'display_name' => 'Foo Permission',
            'description' => 'the description'
        ], $override);
    }
}
