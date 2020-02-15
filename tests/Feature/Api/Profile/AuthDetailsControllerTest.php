<?php

namespace Tests\Feature\Http\Controllers\Api\Profile;

use Facades\Tests\Setup\UserFactory;
use Tests\Feature\ApiTestCase;

class AuthDetailsControllerTest extends ApiTestCase
{
    /** @test */
    public function userCanUpdateHisAuthenticationDetails()
    {
        $user = $this->login();

        $this->patch('/api/me/details/auth', [
            'email' => 'foo@example.com',
            'username' => 'john.doe',
            'password' => '12345678',
            'password_confirmation' => '12345678'
        ])->assertOk()
            ->assertJson(['email' => 'foo@example.com', 'username' => 'john.doe']);

        $this->assertTrue(password_verify('12345678', $user->fresh()->password));
    }

    /** @test */
    public function userCanUpdateOnlyEmailAndLeaveOtherFieldsUnchanged()
    {
        $user = $this->login();

        $this->patch('/api/me/details/auth', [
            'email' => 'foo@example.com',
        ])->assertOk()
            ->assertJson(['email' => 'foo@example.com']);

        $this->assertEquals($user->username, $user->fresh()->username);
        $this->assertEquals($user->password, $user->fresh()->password);
    }

    /** @test */
    public function emailFieldIsRequired()
    {
        $this->login();

        $this->patch('/api/me/details/auth')
            ->assertJsonValidationErrors('email');
    }

    /** @test */
    public function emailFieldMustBeValidEmail()
    {
        $this->login();

        $this->patch('/api/me/details/auth', [
            'email' => 'invalid email'
        ])->assertJsonValidationErrors('email');
    }

    /** @test */
    public function emailFieldMustBeUnique()
    {
        $this->login();

        UserFactory::email('john.doe@test.com')->create();

        $this->patch('/api/me/details/auth', [
            'email' => 'john.doe@test.com',
        ])->assertJsonValidationErrors('email');
    }

    /** @test */
    public function usernameFieldMustBeUnique()
    {
        $this->login();

        UserFactory::withCredentials('john.doe', '123123')->create();

        $this->patch('/api/me/details/auth', [
            'email' => 'john.doe@test.com',
            'username' => 'john.doe'
        ])->assertJsonValidationErrors('username');
    }
}
