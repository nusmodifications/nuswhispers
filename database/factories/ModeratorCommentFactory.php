<?php

$factory->define(App\Models\ModeratorComment::class, function ($faker) {
    return [
        'confession_id' => function () {
            return factory(App\Models\Confession::class)->create()->getKey();
        },
        'user_id' => function () {
            return factory(App\Models\User::class)->create()->getKey();
        },
        'content' => $faker->text()
    ];
});


