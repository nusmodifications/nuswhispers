<?php

$factory->define(App\Models\Tag::class, function ($faker) {
    return [
        'confession_tag' => ucfirst($faker->word),
    ];
});
