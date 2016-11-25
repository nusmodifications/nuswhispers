<?php

$factory->define(App\Models\User::class, function ($faker) {
    return [
        'role' => $faker->randomElement(['Moderator', 'Administrator']),
        'name' => $faker->name(),
        'email' => $faker->email(),
        'password' => Hash::make($faker->password()),
    ];
});

$factory->state(App\Models\User::class, 'moderator', function ($faker) {
    return [
        'role' => 'Moderator',
    ];
});

$factory->state(App\Models\User::class, 'admin', function ($faker) {
    return [
        'role' => 'Administrator',
    ];
});
