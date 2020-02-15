<?php

use Blegrator\Service;
use Blegrator\Servicetype;
use Faker\Generator as Faker;

$factory->define(Service::class, function (Faker $faker) {
    return [
        'servicetype_id' => Servicetype::all()->random()->id,
        'name' => $faker->name(),
        'coverphoto' => null,
        'icon' => '',
        'description' => $faker->sentence(),
        'avg_price' => rand(200, 900),
        'is_active' => true
    ];
});
