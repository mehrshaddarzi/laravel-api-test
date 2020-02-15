<?php

namespace Tests\Feature\Http\Controllers\Api;

use Tests\Feature\ApiTestCase;
use Blegrator\Country;
use Blegrator\Transformers\CountryTransformer;

class CountriesControllerTest extends ApiTestCase
{
    /** @test */
    public function unauthenticated()
    {
        $this->getJson('/api/countries')->assertStatus(401);
    }

    /** @test */
    public function getAllCountries()
    {
        $this->login();

        $transformed = $this->transformCollection(
            Country::all(),
            new CountryTransformer
        );

        $this->getJson("/api/countries")
            ->assertOk()
            ->assertJson($transformed);
    }
}
