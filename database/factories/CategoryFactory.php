<?php

$factory->define(App\Models\Category::class, function ($faker) {
    return [
        'confession_category' => ucfirst($faker->word)
    ];
});
