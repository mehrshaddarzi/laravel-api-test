<?php

namespace Tests\Feature\Http\Controllers\Api\Profile;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Tests\Feature\ApiTestCase;
use Blegrator\Repositories\Session\SessionRepository;
use Blegrator\Transformers\SessionTransformer;
use Blegrator\User;

class SessionsControllerTest extends ApiTestCase
{
    /** @test */
    public function getUserSessionsUnauthenticated()
    {
        $this->getJson('/api/me/sessions')->assertStatus(401);
    }

    /** @test */
    public function getSessionsIfNonDatabaseDriverIsUsed()
    {
        config(['session.driver' => 'array']);

        $this->login();

        $this->getJson('/api/me/sessions')->assertStatus(404);
    }

    /** @test */
    public function getUserSessions()
    {
        config(['session.driver' => 'database']);

        $user = $this->login();

        $sessions = $this->generateNonExpiredSessions($user);

        $this->getJson('/api/me/sessions')
            ->assertOk()
            ->assertJson(
                $this->transformCollection(collect($sessions), new SessionTransformer)
            );
    }

    private function generateNonExpiredSessions(User $user, $count = 5)
    {
        $sessions = [];
        $faker = $this->app->make(\Faker\Generator::class);

        for ($i = 0; $i < $count; $i++) {
            array_push($sessions, [
                'id' => Str::random(40),
                'user_id' => $user->id,
                'ip_address' => $faker->ipv4,
                'user_agent' => $faker->userAgent,
                'payload' => Str::random(),
                'last_activity' => Carbon::now()->subMinute()->timestamp
            ]);
        }

        \DB::table('sessions')->insert($sessions);

        return app(SessionRepository::class)->getUserSessions($user->id);
    }
}
