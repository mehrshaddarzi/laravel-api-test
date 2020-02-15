<?php

namespace Tests\Feature\Http\Controllers\Api\Auth\Password;

use Mail;
use Tests\Feature\ApiTestCase;
use Blegrator\Mail\ResetPassword;
use Blegrator\User;

class RemindControllerTest extends ApiTestCase
{
    /** @test */
    public function sendPasswordReminder()
    {
        $this->setSettings(['forgot_password' => true]);

        Mail::fake();

        $user = factory(User::class)->create(['email' => 'test@test.com']);

        $this->postJson('api/password/remind', ['email' => 'test@test.com'])
            ->assertOk();

        Mail::assertQueued(ResetPassword::class, function ($mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }

    /** @test */
    public function passwordReminderWithWrongEmail()
    {
        $this->setSettings(['forgot_password' => true]);

        $this->postJson('api/password/remind', ['email' => 'test@test.com'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('email');
    }
}
