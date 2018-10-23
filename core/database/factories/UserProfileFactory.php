<?php

$factory->define(\NUSWhispers\Models\UserProfile::class, function ($faker) {
    return [
        'user_id' => function () {
            return factory(\NUSWhispers\Models\User::class)->create()->getKey();
        },
        'provider_name' => $faker->word . microtime(),
        'provider_id' => $faker->word,
    ];
});
