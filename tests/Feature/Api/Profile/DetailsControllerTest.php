<?php

namespace Tests\Feature\Http\Controllers\Api\Profile;

use Carbon\Carbon;
use Tests\Feature\ApiTestCase;
use Blegrator\Transformers\UserTransformer;

class DetailsControllerTest extends ApiTestCase
{
    /** @test */
    public function getUserProfileUnauthenticated()
    {
        $this->getJson('/api/me')->assertStatus(401);
    }

    /** @test */
    public function getUserProfile()
    {
        $user = $this->login();

        $transformed = (new UserTransformer)->transform($user);

        $this->getJson('/api/me')
            ->assertOk()
            ->assertJson($transformed);
    }

    /** @test */
    public function updateUserProfileUnauthenticated()
    {
        $this->patchJson('/api/me/details')->assertStatus(401);
    }

    /** @test */
    public function updateUserProfile()
    {
        $user = $this->login();

        $data = $this->getData();

        $response = $this->patchJson("/api/me/details", $data);

        $transformed = (new UserTransformer)->transform($user->fresh());

        $response->assertJson($transformed);

        $this->assertDatabaseHas('users', array_merge($data, ['id' => $user->id]));
    }

    /** @test */
    public function partiallyUpdateUserDetails()
    {
        $user = $this->login();

        $data = [
            'first_name' => 'John',
            'last_name'  => 'Doe'
        ];

        $response = $this->patchJson("/api/me/details", $data);

        $transformed = (new UserTransformer)->transform($user->fresh());

        $response->assertJson($transformed);

        $this->assertDatabaseHas('users', array_merge($data, [
            'id' => $user->id,
            'birthday' => $user->birthday->format('Y-m-d'),
            'phone' => $user->phone,
            'address' => $user->address,
            'country_id' => $user->country_id,
        ]));
    }

    /** @test */
    public function updateWithoutCountryId()
    {
        $user = $this->login();

        $data = $this->getData();

        unset($data['country_id']);

        $response = $this->patchJson("/api/me/details", $data);

        $transformed = (new UserTransformer)->transform($user->fresh());

        $response->assertJson($transformed);

        $this->assertDatabaseHas('users', array_merge($data, ['id' => $user->id]));
    }

    /** @test */
    public function updateWithInvalidDateFormat()
    {
        $this->login();

        $this->patchJson("/api/me/details", ['birthday' => 'foo'])
            ->assertStatus(422)
            ->assertJsonFragment([
                'birthday' => [
                    trans('validation.date', ['attribute' => 'birthday'])
                ]
            ]);
    }

    /**
     * @param array $attrs
     * @return array
     */
    private function getData(array $attrs = [])
    {
        return array_merge([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'birthday' => Carbon::now()->subYears(25)->format('Y-m-d'),
            'phone' => '(123) 456 789',
            'address' => 'some address 1',
            'country_id' => 688,
        ], $attrs);
    }
}
