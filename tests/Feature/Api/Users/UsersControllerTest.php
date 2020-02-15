<?php

namespace Tests\Feature\Http\Controllers\Api\Users;

use Facades\Tests\Setup\UserFactory;
use Illuminate\Support\Arr;
use Tests\Feature\ApiTestCase;
use Blegrator\Country;
use Blegrator\Events\User\Deleted;
use Blegrator\Events\User\UpdatedByAdmin;
use Blegrator\Role;
use Blegrator\Support\Enum\UserStatus;
use Blegrator\Transformers\UserTransformer;
use Blegrator\User;

class UsersControllerTest extends ApiTestCase
{
    /** @test */
    public function onlyAuthenticatedUsersCanListAllUsers()
    {
        $this->getJson('/api/users')->assertStatus(401);
    }

    /** @test */
    public function getUsersWithoutPermission()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/users')
            ->assertStatus(403);
    }

    /** @test */
    public function paginateAllUsers()
    {
        \DB::table('users')->delete();

        $user = $this->login();

        $users = factory(User::class)->times(20)->create();
        $users->push($user);

        $response = $this->getJson('/api/users');

        $transformed = $this->transformCollection(
            $users->sortByDesc('id')->take(20),
            new UserTransformer
        );

        $this->assertEquals($response->original['data'], $transformed);
        $this->assertEquals($response->original['meta'], [
            'current_page' => 1,
            'from' => 1,
            'to' => 20,
            'last_page' => 2,
            'prev_page_url' => null,
            'next_page_url' => url("api/users?page=2"),
            'total' => 21,
            'per_page' => 20
        ]);
    }

    /** @test */
    public function paginateUsersWithCountryIncluded()
    {
        $this->login();

        $country = factory(Country::class)->create();

        factory(User::class)->create(['country_id' => null]);
        factory(User::class)->create(['country_id' => $country->id]);

        $response = $this->getJson('/api/users?include=country')
            ->assertOk()
            ->original;

        $this->assertNotNull($response['data'][0]['country']['id']);

        $this->assertNull($response['data'][1]['country_id']);
        $this->assertArrayNotHasKey('country', $response['data'][1]);

        $this->assertNotNull($response['data'][2]['country']['id']);
    }

    /** @test */
    public function paginateUsersByStatus()
    {
        $this->login();

        factory(User::class)->times(2)->create(['status' => UserStatus::ACTIVE]);
        factory(User::class)->times(5)->create(['status' => UserStatus::BANNED]);

        $response = $this->getJson('/api/users?status=' . UserStatus::BANNED);

        $this->assertCount(5, $response->original['data']);
    }

    /** @test */
    public function paginateUsersOnSearch()
    {
        $user = $this->login();

        $user1 = factory(User::class)->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@blegratorapp.io'
        ]);

        $user2 = factory(User::class)->create([
            'first_name' => 'Jane',
            'last_name' => 'Doe',
            'email' => 'jane.doe@blegratorapp.io'
        ]);

        $user3 = factory(User::class)->create([
            'first_name' => 'Brad',
            'last_name' => 'Pitt',
            'email' => 'b.pitt@blegratorapp.io'
        ]);

        $response = $this->getJson('/api/users?search=doe');

        $this->assertCount(2, $response->original['data']);
    }

    /** @test */
    public function createUser()
    {
        $this->login();

        $newUser = factory(User::class)->make();

        $data = array_merge($newUser->toArray(), [
            'birthday' => $newUser->birthday->format('Y-m-d'),
            'role' => $newUser->role_id,
            'password' => '123123',
            'password_confirmation' => '123123'
        ]);

        $response = $this->postJson('api/users', $data);

        $expected = [
            'first_name' => $newUser->first_name,
            'last_name' => $newUser->last_name,
            'email' => $newUser->email,
            'username' => $newUser->username,
            'country_id' => $newUser->country_id,
            'birthday' => $newUser->birthday->format('Y-m-d'),
            'phone' => $newUser->phone,
            'address' => $newUser->address,
            'status' => UserStatus::ACTIVE,
            'role_id' => $newUser->role_id
        ];

        $response->assertStatus(201)
            ->assertJson($expected);

        $this->assertDatabaseHas('users', $expected);
    }

    /** @test */
    public function getUser()
    {
        $user = $this->login();

        $this->getJson("api/users/{$user->id}")
            ->assertOk()
            ->assertJson(
                (new UserTransformer)->transform($user)
            );
    }

    /** @test */
    public function getUserWhichDoesNotExist()
    {
        $this->login();

        $this->getJson("api/users/222")->assertStatus(404);
    }

    /** @test */
    public function updateUser()
    {
        $this->expectsEvents(UpdatedByAdmin::class);

        $user = $this->login();

        $data = [
            'email' => 'john.doe@test.com',
            'username' => 'john.doe',
            'password' => '123123',
            'password_confirmation' => '123123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'phone' => '+38123456789',
            'address' => 'Some random address',
            'country_id' => Country::first()->id,
            'birthday' => '1990-10-18',
            'status' => UserStatus::BANNED,
            'role_id' => Role::whereName('Customer')->first()->id
        ];

        $expected = Arr::except($data, ['password', 'password_confirmation']);
        $expected += ['id' => $user->id];

        $this->patchJson("api/users/{$user->id}", $data)
            ->assertOk()
            ->assertJson($expected);

        $this->assertDatabaseHas('users', $expected);
    }

    /** @test */
    public function updateOnlySpecificField()
    {
        $this->expectsEvents(UpdatedByAdmin::class);

        $user = $this->login();

        $data = ['email' => 'john.doe@test.com'];

        $expected = array_merge(
            $user->toArray(),
            $data,
            ['birthday' => $user->birthday->format('Y-m-d')]
        );

        $expected = Arr::except($expected, ['created_at', 'updated_at', 'avatar', 'role']);

        $this->patchJson("api/users/{$user->id}", $data)
            ->assertOk()
            ->assertJson($expected);

        $this->assertDatabaseHas('users', $expected);
    }

    /** @test */
    public function deleteUser()
    {
        $this->expectsEvents(Deleted::class);

        $user = $this->login();

        $user2 = factory(User::class)->create();

        $this->deleteJson("api/users/{$user2->id}")
            ->assertOk()
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function deleteYourself()
    {
        $user = $this->login();

        $this->deleteJson("api/users/{$user->id}")
            ->assertStatus(403)
            ->assertJson(['error' => "You cannot delete yourself."]);
    }

    protected function login()
    {
        $user = UserFactory::withPermissions('users.manage')->create();

        $this->be($user, 'api');

        return $user;
    }
}
