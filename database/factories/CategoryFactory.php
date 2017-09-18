<?php

$factory->define(\NUSWhispers\Models\Category::class, function ($faker) {
    return [
        'confession_category' => ucfirst($faker->word) . microtime(true),
    ];
});
