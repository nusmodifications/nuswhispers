<?php

$factory->define(\NUSWhispers\Models\ConfessionLog::class, function ($faker) {
    $statusBefore = $faker->randomElement(\NUSWhispers\Models\Confession::statuses());
    $statusAfter = $faker->randomElement(array_except(\NUSWhispers\Models\Confession::statuses(), $statusBefore));

    return [
        'confession_id' => function () use ($statusAfter) {
            return factory(\NUSWhispers\Models\Confession::class)->create([
                'status' => $statusAfter,
            ])->getKey();
        },
        'changed_by_user' => function () {
            return factory(\NUSWhispers\Models\User::class)->create()->getKey();
        },
        'status_before' => $statusBefore,
        'status_after' => $statusAfter,
    ];
});
