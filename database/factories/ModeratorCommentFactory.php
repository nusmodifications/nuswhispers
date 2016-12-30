<?php

$factory->define(\NUSWhispers\Models\ModeratorComment::class, function ($faker) {
    return [
        'confession_id' => function () {
            return factory(\NUSWhispers\Models\Confession::class)->create()->getKey();
        },
        'user_id' => function () {
            return factory(\NUSWhispers\Models\User::class)->create()->getKey();
        },
        'content' => $faker->text(),
    ];
});
