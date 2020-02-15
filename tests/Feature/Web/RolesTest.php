<?php

namespace Tests\Feature\Http\Controllers\Web;

use Facades\Tests\Setup\UserFactory;
use Facades\Tests\Setup\RoleFactory;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Blegrator\Role;

class RolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    /** @test */
    public function guestsCannotViewRoleList()
    {
        $this->get('/roles')->assertRedirect('/login');
    }

    /** @test */
    public function usersWithoutAppropriatePermissionCannotViewRoleList()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('roles.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $this->actingAs($userA)->get('/roles')->assertStatus(403);
        $this->actingAs($userB)->get('/roles')->assertOk();
    }

    /** @test */
    public function rolesListIsDisplayedProperly()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::create();

        $roles = $this->actingAsAdmin()->get('/roles')->viewData('roles');

        $this->assertCount(6, $roles); // 3 default roles are created when db is seeded
        $this->assertTrue($roles->contains($roleA));
        $this->assertTrue($roles->contains($roleB));
    }

    /** @test */
    public function createRole()
    {
        $data = factory(Role::class)->raw();

        $this->actingAsAdmin()
            ->post('/roles', $data)
            ->assertRedirect('/roles');

        $this->assertSessionHasSuccess('Role created successfully.');
        $this->assertDatabaseHas('roles', $data);
    }

    /** @test */
    public function usersWithoutAppropriatePermissionCannotCreateNewRoles()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('roles.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $data = factory(Role::class)->raw();

        $this->actingAs($userA)
            ->post('/roles', $data)
            ->assertStatus(403);

        $this->assertDatabaseMissing('roles', $data);

        $this->actingAs($userB)
            ->post('/roles', $data)
            ->assertRedirect('roles');

        $this->assertDatabaseHas('roles', $data);
    }

    /** @test */
    public function updateRole()
    {
        $role = factory(Role::class)->create(['name' => 'foo']);

        $this->actingAsAdmin()
            ->get("roles/{$role->id}/edit")
            ->assertOk()
            ->assertSee($role->name)
            ->assertSee($role->display_name)
            ->assertSee($role->description);

        $data = factory(Role::class)->raw();

        $this->put("/roles/{$role->id}", $data);

        $this->assertSessionHasSuccess('Role updated successfully.');
        $this->assertDatabaseHas('roles', $data + ['id' => $role->id]);
    }

    /** @test */
    public function usersWithoutAppropriatePermissionCannotUpdateRole()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('roles.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $role = factory(Role::class)->create(['name' => 'foo']);

        $data = factory(Role::class)->raw();

        $this->actingAs($userA)
            ->put("/roles/{$role->id}", $data)
            ->assertStatus(403);

        $this->assertEquals($role->toArray(), $role->fresh()->toArray());

        $this->actingAs($userB)
            ->put("/roles/{$role->id}", $data)
            ->assertRedirect('roles');

        $role->refresh();

        $this->assertEquals($role->name, $data['name']);
        $this->assertEquals($role->display_name, $data['display_name']);
        $this->assertEquals($role->description, $data['description']);
    }

    /** @test */
    public function removableAttributeCannotBeChangedOnUpdate()
    {
        $role = RoleFactory::unremovable()->create();

        $data = factory(Role::class)->raw(['removable' => true]);

        $this->actingAsAdmin()->put("/roles/{$role->id}", $data);

        $role->refresh();

        $this->assertEquals($role->name, $data['name']);
        $this->assertEquals($role->display_name, $data['display_name']);
        $this->assertEquals($role->description, $data['description']);
        $this->assertFalse($role->removable);
    }

    /** @test */
    public function deleteRole()
    {
        $role = RoleFactory::removable()->create();

        $this->actingAsAdmin()->delete(route('roles.destroy', $role));

        $this->assertNull($role->fresh());
    }

    /** @test */
    public function usersWithoutAppropriatePermissionCannotDeleteRole()
    {
        $roleA = RoleFactory::create();
        $roleB = RoleFactory::withPermissions('roles.manage')->create();

        $userA = UserFactory::role($roleA)->create();
        $userB = UserFactory::role($roleB)->create();

        $role = factory(Role::class)->create(['name' => 'foo']);

        $this->actingAs($userA)
            ->delete(route('roles.destroy', $role->id))
            ->assertStatus(403);

        $this->assertNotNull($role->fresh());

        $this->actingAs($userB)
            ->delete(route('roles.destroy', $role->id))
            ->assertRedirect('roles');

        $this->assertNull($role->fresh());
    }

    /** @test */
    public function usersReceiveDefaultRoleAfterTheirRoleIsDeleted()
    {
        $user = UserFactory::create();
        $role = factory(Role::class)->create(['removable' => true]);
        $userRole = Role::where('name', 'Customer')->first();

        $user->setRole($role);

        $this->assertTrue($user->fresh()->hasRole($role->name));

        $this->actingAsAdmin()->delete(route('roles.destroy', $role));

        $this->assertDatabaseHas('users', [
            'role_id' => $userRole->id,
            'id' => $user->id
        ]);

        $user = $user->fresh();

        $this->assertFalse($user->hasRole($role->name));
        $this->assertTrue($user->hasRole($userRole->name));
    }

    /** @test */
    public function onlyRemovableRolesCanBeDeleted()
    {
        $removableRole = factory(Role::class)->create(['removable' => true]);
        $nonRemovableRole = factory(Role::class)->create(['removable' => false]);

        $this->beAdmin();

        $this->delete(route('roles.destroy', $removableRole->id));

        $this->assertNull($removableRole->fresh());

        $this->delete(route('roles.destroy', $nonRemovableRole->id))
            ->assertStatus(404);

        $this->assertNotNull($nonRemovableRole->fresh());
    }
}
