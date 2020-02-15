<?php

namespace Tests\Feature\Http\Controllers\Api\Profile;

use Illuminate\Http\UploadedFile;
use Storage;
use Tests\Feature\ApiTestCase;

class AvatarControllerTest extends ApiTestCase
{
    /** @test */
    public function onlyAuthenticatedUserCanUpdateAvatar()
    {
        $this->putJson('/api/me/avatar')->assertStatus(401);
    }

    /** @test */
    public function uploadAvatarImage()
    {
        $this->login();

        Storage::fake('public');

        $file = UploadedFile::fake()->image('avatar.png', 500, 500);

        $fileContent = file_get_contents($file->getRealPath());

        $server = $this->transformHeadersToServerVars([
            'Accept' => 'application/json',
            'Content-Type' => 'image/jpeg'
        ]);

        $response = $this->call('PUT', '/api/me/avatar', [], [], [], $server, $fileContent)
            ->assertOk();

        $this->assertNotNull($response->original['avatar']);

        $uploadedFile = str_replace(url(''), '', $response->original['avatar']);
        $uploadedFile = ltrim($uploadedFile, "/");

        Storage::disk('public')->assertExists($uploadedFile);

        list($width, $height) = getimagesizefromstring(
            Storage::disk('public')->get($uploadedFile)
        );

        $this->assertEquals(160, $width);
        $this->assertEquals(160, $height);
    }

    /** @test */
    public function uploadInvalidImage()
    {
        $this->login();

        Storage::fake('public');

        $file = UploadedFile::fake()->create('avatar.png', 500);

        $fileContent = file_get_contents($file->getRealPath());

        $server = $this->transformHeadersToServerVars([
            'Accept' => 'application/json',
            'Content-Type' => 'image/jpeg'
        ]);

        $this->call('PUT', '/api/me/avatar', [], [], [], $server, $fileContent)
            ->assertStatus(422)
            ->assertJsonFragment([
                'file' => [
                    trans('validation.image', ['attribute' => 'file'])
                ]
            ]);
    }

    /** @test */
    public function updateAvatarFromExternalSource()
    {
        $this->login();

        $url = 'http://google.com';

        $this->putJson('/api/me/avatar/external', [
            'url' => $url
        ])->assertOk()
            ->assertJson(['avatar' => $url]);
    }

    /** @test */
    public function updateAvatarWithInvalidExternalSource()
    {
        $this->login();

        $this->putJson('/api/me/avatar/external', [
            'url' => 'foo'
        ])->assertStatus(422);
    }

    /** @test */
    public function deleteAvatar()
    {
        $user = $this->login();

        $user->forceFill(['avatar' => 'http://google.com'])->save();

        $this->deleteJson("api/me/avatar")
            ->assertOk()
            ->assertJson([
                'avatar' => url('assets/img/profile.png') // default profile image
            ]);
    }
}
