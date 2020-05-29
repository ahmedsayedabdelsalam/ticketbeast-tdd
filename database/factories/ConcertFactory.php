<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use Faker\Generator as Faker;
use App\Concert;

$factory->define(Concert::class, function (Faker $faker) {
    return [
        'title' => $faker->title,
        'subtitle' => $faker->sentence,
        'date' => $faker->dateTime,
        'ticket_price' => $faker->randomNumber(4),
        'venue' => $faker->sentence(3),
        'venue_address' => $faker->address,
        'city' => $faker->city,
        'state' => $faker->state,
        'zip' => $faker->postcode,
        'additional_information' => $faker->paragraph
    ];
});

$factory->state(Concert::class, 'published', function (Faker $faker) {
    return [
        'published_at' => $faker->dateTimeThisMonth,
    ];
});

$factory->state(Concert::class, 'unpublished', function (Faker $faker) {
    return [
        'published_at' => null,
    ];
});

