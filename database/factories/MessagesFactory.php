<?php

/** @var Factory $factory */


use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use Wovosoft\LaravelMessenger\Models\Messages;

$factory->define(Messages::class, function (Faker $faker) {
    return [
        "sender_id" => random_int(1, 50),
        "sender_type" => \App\User::class,
        "receiver_id" => random_int(1, 50),
        "receiver_type" => \App\User::class,
        "message" => $faker->text
    ];
});
