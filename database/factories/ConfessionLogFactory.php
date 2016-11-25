<?php

$factory->define(App\Models\ConfessionLog::class, function ($faker) {
    $statusBefore = $faker->randomElement(App\Models\Confession::statuses());
    $statusAfter = $faker->randomElement(array_except(App\Models\Confession::statuses(), $statusBefore));

    return [
        'confession_id' => function () use ($statusAfter) {
            return factory(App\Models\Confession::class)->create([
                'status' => $statusAfter
            ])->getKey();
        },
        'changed_by_user' => function () {
            return factory(App\Models\User::class)->create()->getKey();
        },
        'status_before' => $statusBefore,
        'status_after' => $statusAfter
    ];
});
