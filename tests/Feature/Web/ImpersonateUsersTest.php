<?php

namespace Tests\Feature\Web;

use Facades\Tests\Setup\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Blegrator\User;

class ImpersonateUsersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed --class=RolesSeeder');
        $this->artisan('db:seed --class=PermissionsSeeder');
    }

    /** @test */
    public function unverifiedUsersCannotImpersonateOtherUsers()
    {
        $user = UserFactory::withPermissions('users.manage')->unverified()->create();

        factory(User::class)->create();

        $this->actingAs($user)->get('/users')->assertRedirect("/email/verify");
    }

    /** @test */
    public function aUserWithAppropriatePermissionCanImpersonateOtherUsersFromAUserListPage()
    {
        $user = UserFactory::withPermissions('users.manage')->create();

        factory(User::class)->create();

        $this->actingAs($user)->get('/users')->assertSee("Impersonate");
    }

    /** @test */
    public function aUserDontSeeImpersonateButtonNextToHisNameInTheUserList()
    {
        $user = UserFactory::withPermissions('users.manage')->create();

        $this->actingAs($user)->get('/user')->assertDontSee("Impersonate");
    }

    /** @test */
    public function clickingOnImpersonateButtonWillImpersonateTheUser()
    {
        $userA = UserFactory::withPermissions('users.manage')->create();
        $userB = UserFactory::user()->create();

        $this->actingAs($userA)
            ->get(route('impersonate', $userB))
            ->assertRedirect("/");

        $this->assertTrue(auth()->user()->is($userB));
    }

    /** @test */
    public function whileImpersonatingUserCanStopImpersonatingByClickingOnTheHeaderButton()
    {
        $userA = UserFactory::withPermissions('users.manage')->create();
        $userB = UserFactory::user()->create();

        $this->actingAs($userA)->get(route('impersonate', $userB));

        $this->assertTrue(auth()->user()->is($userB));

        $this->get("/")->assertSee("Stop Impersonating");

        $this->get(route('impersonate.leave'));

        $this->assertTrue(auth()->user()->is($userA));
    }

    /** @test */
    public function whileImpersonatingUserCannotImpersonateOtherUserEvenIfHeHasAPermission()
    {
        $userA = UserFactory::withPermissions('users.manage')->create();
        $userB = UserFactory::withPermissions('users.manage')->create();

        $this->actingAs($userA)->get(route('impersonate', $userB));

        $this->get("/user")
            ->assertDontSee("Impersonate");
    }
}
