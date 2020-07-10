<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Concert;
use App\Ticket;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Ticket::class, function (Faker $faker) {
    return [
        'concert_id' => function() {
            return factory(Concert::class)->create();
        }
    ];
});

$factory->state(Ticket::class, 'reserved', function (Faker $faker) {
    return [
        'reserved_at' => Carbon::now()
    ];
});
