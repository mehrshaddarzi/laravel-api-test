<?php

namespace Tests\Feature\Http\Controllers\Web;

use Authy;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\UpdatesSettings;
use Blegrator\Events\User\TwoFactorEnabled;
use Blegrator\Events\User\TwoFactorEnabledByAdmin;
use Blegrator\Repositories\User\UserRepository;
use Blegrator\User;

class TwoFactorAuthTest extends TestCase
{
    use RefreshDatabase, UpdatesSettings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed');
    }

    /** @test */
    public function the2FaFormIsVisibleOnProfilePageIf2FaIsEnabled()
    {
        config(['services.authy.key' => 'test']);

        $this->setSettings(['2fa.enabled' => false]);

        $this->actingAsAdmin()
            ->get("profile")
            ->assertDontSee('Two-Factor Authentication');

        $this->setSettings(['2fa.enabled' => true]);

        $this->actingAsAdmin()
            ->get("profile")
            ->assertSee('Two-Factor Authentication');
    }

    /** @test */
    public function the2FaFormIsVisibleOnEditUserPageIf2FaIsEnabled()
    {
        config(['services.authy.key' => 'test']);

        $this->setSettings(['2fa.enabled' => false]);

        $user = UserFactory::create();

        $this->actingAsAdmin()
            ->get("/users/{$user->id}/edit")
            ->assertDontSee('Two-Factor Authentication');

        $this->setSettings(['2fa.enabled' => true]);

        $this->actingAsAdmin()
            ->get("/users/{$user->id}/edit")
            ->assertSee('Two-Factor Authentication');
    }

    /** @test */
    public function enable2FaFromProfilePage()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user = UserFactory::user()->create();

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('register')->andReturnNull();
        Authy::shouldReceive('sendTwoFactorVerificationToken')->andReturnNull();

        $this->actingAs($user)
            ->post('/two-factor/enable', ['country_code' => '1', 'phone_number' => '123'])
            ->assertRedirect('/two-factor/verification');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_country_code' => 1,
            'two_factor_phone' => 123
        ]);
    }

    /** @test */
    public function enable2FaFromEditUserPage()
    {
        $this->setSettings(['2fa.enabled' => true]);

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('register')->andReturnNull();
        Authy::shouldReceive('sendTwoFactorVerificationToken')->andReturnNull();

        $user = UserFactory::user()->create();
        $formData = ['country_code' => '1', 'phone_number' => '123', 'user' => $user->id];

        $this->actingAsAdmin()
            ->post("users/{$user->id}/two-factor/enable", $formData)
            ->assertRedirect("two-factor/verification?user={$user->id}");

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_country_code' => 1,
            'two_factor_phone' => 123
        ]);
    }

    /** @test */
    public function usersWithoutAppropriatePermissionsCannotEnable2FaForOtherUsers()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('register')->andReturnNull();
        Authy::shouldReceive('sendTwoFactorVerificationToken')->andReturnNull();

        $user = UserFactory::user()->create();

        $this->post('two-factor/enable', [
            'user' => $user->id,
            'country_code' => '1',
            'phone_number' => '123'
        ])->assertStatus(403);

        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
            'two_factor_country_code' => 1,
            'two_factor_phone' => 123
        ]);
    }

    /** @test */
    public function phoneVerificationPageIsNotAccessibleIf2FaIsDisabledOnGlobalLevel()
    {
        $this->setSettings(['2fa.enabled' => false]);

        $this->actingAsAdmin()
            ->get("two-factor/verification")
            ->assertNotFound();
    }

    /** @test */
    public function phoneVerificationPageIsNotAccessibleIfUserPhoneIsNotSet()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user1 = UserFactory::user()->twoFactor(null, null)->create();
        $user2 = UserFactory::user()->twoFactor(1, null)->create();
        $user3 = UserFactory::user()->twoFactor(null, '123456')->create();

        $this->actingAs($user1)->get("two-factor/verification")->assertNotFound();
        $this->actingAs($user2)->get("two-factor/verification")->assertNotFound();
        $this->actingAs($user3)->get("two-factor/verification")->assertNotFound();
    }

    /** @test */
    public function usersWhoHaveAlreadyEnabled2FaCannotViewThePhoneVerificationPage()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        Authy::shouldReceive('isEnabled')->andReturn(true);

        $this->get("two-factor/verification")->assertNotFound();
    }

    /** @test */
    public function usersWhoHaveAlreadyEnabled2FaCannotSubmitEnable2FaForm()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        Authy::shouldReceive('isEnabled')->andReturn(true);

        $this->post("two-factor/enable", ['country_code' => '1', 'phone_number' => '123'])
            ->assertNotFound();
    }

    /** @test */
    public function usersWhoHaveAlreadyEnabled2FaCannotSubmitVerificationForm()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        Authy::shouldReceive('isEnabled')->andReturn(true);

        $this->post("two-factor/verify")->assertNotFound();
    }

    /** @test */
    public function tokenFieldIsRequiredDuring2FaPhoneVerification()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user = UserFactory::user()->twoFactor('1', '123456')->create();

        $this->actingAs($user)
            ->post("two-factor/verify")
            ->assertSessionHasErrors('token');
    }

    /** @test */
    public function the2FaVerificationWithWrongTokenWillFail()
    {
        $this->withoutExceptionHandling();
        $this->setSettings(['2fa.enabled' => true]);

        $user = UserFactory::user()->twoFactor("1", "123123")->create();

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('tokenIsValid')->with($user, "123123")->andReturn(false);

        $this->actingAs($user)
            ->post("two-factor/verify", ['token' => '123123']);

        $this->assertSessionHasError('Invalid 2FA token.');
    }

    /** @test */
    public function successful2FaPhoneVerification()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->expectsEvents(TwoFactorEnabled::class);

        $user = UserFactory::user()->twoFactor("1", "123123")->create();

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('tokenIsValid')->with($user, '123123')->andReturn(true);

        $this->actingAs($user)
            ->post("two-factor/verify", ['token' => '123123'])
            ->assertRedirect("/profile");

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_options' => '{"enabled":true}'
        ]);
    }

    /** @test */
    public function successful2FaPhoneVerificationForOtherUser()
    {
        $this->withoutExceptionHandling();

        $this->setSettings(['2fa.enabled' => true]);

        $this->expectsEvents(TwoFactorEnabledByAdmin::class);

        $user = UserFactory::user()->twoFactor("1", "123123")->create();

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('tokenIsValid')->once()->andReturn(true);

        $this->actingAsAdmin()
            ->post("two-factor/verify", ['token' => '123123', 'user' => $user->id])
            ->assertRedirect("/users/{$user->id}/edit");

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_options' => '{"enabled":true}'
        ]);
    }

    /** @test */
    public function userCannotSubmitPhoneVerificationFormIfPhoneIsNotProvided()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user1 = UserFactory::user()->twoFactor(null, null)->create();
        $user2 = UserFactory::user()->twoFactor(1, null)->create();
        $user3 = UserFactory::user()->twoFactor(null, '123456')->create();

        $this->actingAs($user1)->post("two-factor/verify")->assertNotFound();
        $this->actingAs($user2)->post("two-factor/verify")->assertNotFound();
        $this->actingAs($user3)->post("two-factor/verify")->assertNotFound();
    }

    /** @test */
    public function userCanRequestANewSmsWithACodeOncePerMinute()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user = UserFactory::user()->twoFactor("1", "123123")->create();

        $this->be($user);

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('sendTwoFactorVerificationToken')->once()->andReturn(false);

        $this->post("/two-factor/resend");
        $this->post("/two-factor/resend");
        $this->post("/two-factor/resend");
    }

    /** @test */
    public function onlyUserWithAppropriatePermissionsCanRequestNew2FaTokenForAnotherUser()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        $user = UserFactory::user()->twoFactor("1", "123123")->create();

        $this->post("/two-factor/resend", ['user' => $user->id])
            ->assertStatus(403);
    }

    /** @test */
    public function userCanRequestANewSmsWithACodeOncePerMinuteWhileEnabling2FaForOtherUser()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->beAdmin();

        $user = UserFactory::user()->twoFactor("1", "123123")->create();

        $repo = \Mockery::mock(UserRepository::class);
        $repo->shouldReceive('find')->with($user->id)->andReturn($user);
        $this->app->instance(UserRepository::class, $repo);

        Authy::shouldReceive('isEnabled')->andReturn(false);
        Authy::shouldReceive('sendTwoFactorVerificationToken')->once()->with($user)->andReturn(false);

        $this->post("/two-factor/resend", ['user' => $user->id]);
        $this->post("/two-factor/resend", ['user' => $user->id]);
        $this->post("/two-factor/resend", ['user' => $user->id]);
    }

    /** @test */
    public function usersCannotRequestNewCodesIfTheyAlreadyHave2FaEnabled()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        Authy::shouldReceive('isEnabled')->andReturn(true);
        Authy::shouldReceive('sendTwoFactorVerificationToken')->never();

        $this->post("/two-factor/resend")->assertNotFound();
    }

    /** @test */
    public function userCannotHitResendEndpointIfPhoneIsNotProvided()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $user1 = UserFactory::user()->twoFactor(null, null)->create();
        $user2 = UserFactory::user()->twoFactor(1, null)->create();
        $user3 = UserFactory::user()->twoFactor(null, '123456')->create();

        $this->actingAs($user1)->post("/two-factor/resend")->assertNotFound();
        $this->actingAs($user2)->post("/two-factor/resend")->assertNotFound();
        $this->actingAs($user3)->post("/two-factor/resend")->assertNotFound();
    }

    /** @test */
    public function userCanDisable2Fa()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        $this->expectsEvents(\Blegrator\Events\User\TwoFactorDisabled::class);

        Authy::shouldReceive('isEnabled')->andReturn(true);
        Authy::shouldReceive('delete')->andReturnNull();

        $this->from('/profile')
            ->post('two-factor/disable')
            ->assertRedirect("/profile");

        $this->assertSessionHasSuccess('Two-Factor Authentication disabled successfully.');
    }

    /** @test */
    public function userCanDisable2FaForAnotherUser()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->expectsEvents(\Blegrator\Events\User\TwoFactorDisabled::class);

        $this->beAdmin();

        $user = UserFactory::user()->create();

        Authy::shouldReceive('isEnabled')->andReturn(true);
        Authy::shouldReceive('delete')->andReturnNull();

        $this->from("/users/{$user->id}/edit")
            ->post("users/{$user->id}/two-factor/disable")
            ->assertRedirect("/users/{$user->id}/edit");

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'two_factor_country_code' => null,
            'two_factor_phone' => null
        ]);

        $this->assertSessionHasSuccess('Two-Factor Authentication disabled successfully.');
    }

    /** @test */
    public function userWithoutAppropriatePermissionsCannotDisable2FaForAnotherUser()
    {
        $this->setSettings(['2fa.enabled' => true]);

        $this->be(UserFactory::user()->create());

        $user = factory(User::class)->create();

        Authy::shouldReceive('isEnabled')->andReturn(true);

        $this->post("two-factor/disable", ['user' => $user->id])->assertForbidden();
    }
}
