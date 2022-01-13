<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Order;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'confirmation_number' => 'ORDERCONFIRMATION1234',
        'amount' => 5250,
        'email' => 'somebody@example.com'
    ];
});
