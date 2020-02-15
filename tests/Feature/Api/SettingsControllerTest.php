<?php

namespace Tests\Feature\Http\Controllers\Api;

use Facades\Tests\Setup\UserFactory;
use Setting;
use Tests\Feature\ApiTestCase;
use Blegrator\User;

class SettingsControllerTest extends ApiTestCase
{
    /** @test */
    public function onlyAuthenticatedUsersCanViewAppSettings()
    {
        $this->getJson('/api/settings')->assertStatus(401);
    }

    /** @test */
    public function getSettingsWithoutPermission()
    {
        $user = factory(User::class)->create();

        $this->actingAs($user, 'api')
            ->getJson('/api/settings')
            ->assertStatus(403);
    }

    /** @test */
    public function getSettings()
    {
        $user = UserFactory::withPermissions('settings.general')->create();

        $this->actingAs($user, 'api')
            ->getJson("/api/settings")
            ->assertOk()
            ->assertJson(Setting::all());
    }
}
