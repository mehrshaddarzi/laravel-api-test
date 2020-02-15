<?php

namespace Blegrator\Http\Controllers\Api;

use Blegrator\Repositories\Country\CountryRepository;
use Blegrator\Transformers\CountryTransformer;

/**
 * Class CountriesController
 * @package Blegrator\Http\Controllers\Api
 */
class CountriesController extends ApiController
{
    /**
     * @var CountryRepository
     */
    private $countries;

    public function __construct(CountryRepository $countries)
    {
        $this->middleware('auth');
        $this->countries = $countries;
    }

    /**
     * Get list of all available countries.
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->respondWithCollection(
            $this->countries->all(),
            new CountryTransformer
        );
    }
}
