<?php

$factory->define(\NUSWhispers\Models\ApiKey::class, function ($faker) {
    return [
        'key' => $faker->word,
        'user_id' => function () {
            return factory(\NUSWhispers\Models\User::class)->create()->getKey();
        },
    ];
});
