<?php

use Blegrator\Servicetype;
use Faker\Generator as Faker;

$factory->define(Servicetype::class, function (Faker $faker) {
    // Create Service Folder in assets
    $filepath = public_path('assets/img/services');
    if (!File::exists($filepath)) {
        File::makeDirectory($filepath);
    }

    return [
        'user_id' => DB::table('users')->first()->id,
        //'name' => $faker->name(),
        'coverphoto' => null,
        'icon' => $faker->image($filepath, 400, 300, 'food', false),
        'description' => $faker->sentence()
    ];
});
