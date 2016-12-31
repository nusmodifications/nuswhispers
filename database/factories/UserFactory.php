<?php

$factory->define(\NUSWhispers\Models\User::class, function ($faker) {
    return [
        'role' => $faker->randomElement(['Moderator', 'Administrator']),
        'name' => $faker->name(),
        'email' => $faker->email(),
        'password' => Hash::make($faker->password()),
    ];
});

$factory->state(\NUSWhispers\Models\User::class, 'moderator', function ($faker) {
    return [
        'role' => 'Moderator',
    ];
});

$factory->state(\NUSWhispers\Models\User::class, 'admin', function ($faker) {
    return [
        'role' => 'Administrator',
    ];
});
