<?php

namespace Tests\Feature\Http\Controllers\Api\Authorization;

use Carbon\Carbon;
use Facades\Tests\Setup\RoleFactory;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Support\Collection;
use Tests\Feature\ApiTestCase;
use Tests\Feature\FunctionalOldTestCase;
use Blegrator\Country;
use Blegrator\Role;
use Blegrator\Services\Logging\UserActivity\Activity;
use Blegrator\Support\Enum\UserStatus;
use Blegrator\Transformers\ActivityTransformer;
use Blegrator\Transformers\RoleTransformer;
use Blegrator\Transformers\SessionTransformer;
use Blegrator\Transformers\UserTransformer;
use Blegrator\User;

class RolesControllerTest extends ApiTestCase
{
    /** @test */
    public function unauthenticated()
    {
        $this->getJson('/api/roles')
            ->assertStatus(401);
    }

    /** @test */
    public function getSettingsWithoutPermission()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/roles')
            ->assertStatus(403);
    }

    /** @test */
    public function getRoles()
    {
        factory(Role::class)->times(4)->create();

        $roles = Role::withCount('users')->get();

        $response = $this->actingAs($this->getUser(), 'api')
            ->getJson("/api/roles")
            ->assertOk()
            ->assertJson(
                $this->transformCollection($roles, new RoleTransformer)
            );

        $this->assertCount(9, $response->original);
    }

    /** @test */
    public function getRole()
    {
        $userRole = Role::whereName('Customer')->first();

        $this->actingAs($this->getUser(), 'api')
            ->getJson("/api/roles/{$userRole->id}")
            ->assertOk()
            ->assertJson(
                (new RoleTransformer)->transform($userRole)
            );
    }

    /** @test */
    public function createRole()
    {
        $this->getUser();

        $data = [
            'name' => 'foo',
            'display_name' => 'Foo Role',
            'description' => 'This is foo role.'
        ];

        $this->actingAs($this->getUser(), 'api')
            ->postJson("/api/roles", $data)
            ->assertOk()
            ->assertJson($data);

        $this->assertDatabaseHas('roles', $data);
    }

    /** @test */
    public function createRoleWithInvalidName()
    {
        $this->be($this->getUser(), 'api');

        $this->postJson("/api/roles")
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->postJson("/api/roles", ['name' => 'Customer'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');

        $this->postJson("/api/roles", ['name' => 'foo bar'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    /** @test */
    public function updateRole()
    {
        $user = $this->getUser();

        $data = ['name' => 'foo'];
        $expected = $data + ['id' => $user->role_id];

        $this->actingAs($user, 'api')
            ->patchJson("/api/roles/{$user->role_id}", $data)
            ->assertOk()
            ->assertJson($expected);

        $this->assertDatabaseHas('roles', $expected);
    }

    /** @test */
    public function partiallyUpdateRole()
    {
        $user = $this->getUser();

        $data = [
            'name' => 'foo',
            'display_name' => 'Foo Role',
            'description' => 'This is foo role.'
        ];
        $expected = $data + ['id' => $user->role_id];

        $this->actingAs($user, 'api')
            ->patchJson("/api/roles/{$user->role_id}", $data)
            ->assertOk()
            ->assertJson($expected);

        $this->assertDatabaseHas('roles', $expected);
    }

    /** @test */
    public function removeRole()
    {
        $userRole = Role::whereName('Customer')->first();
        $role = RoleFactory::removable()->withPermissions('roles.manage')->create();
        $user = UserFactory::role($role)->create();

        $this->actingAs($user, 'api')
            ->deleteJson("/api/roles/{$role->id}")
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseMissing('roles', ['id' => $role->id]);
        $this->assertEquals($userRole->id, $user->fresh()->role_id);
    }

    /** @test */
    public function removeNonRemovableRole()
    {
        $role = RoleFactory::withPermissions('roles.manage')->create();

        $this->actingAs($this->getUser(), 'api')
            ->deleteJson("/api/roles/{$role->id}")
            ->assertForbidden();
    }

    /**
     * @return mixed
     */
    private function getUser()
    {
        return UserFactory::user()->withPermissions('roles.manage')->create();
    }
}
