<?php

$factory->define(\NUSWhispers\Models\ConfessionQueue::class, function ($faker) {
    return [
        'confession_id' => function () {
            return factory(\NUSWhispers\Models\Confession::class)->create([
                'status' => 'Pending',
            ])->getKey();
        },
        'status_after' => $faker->randomElement(['Approved', 'Featured', 'Rejected']),
    ];
});
