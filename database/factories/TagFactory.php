<?php

$factory->define(\NUSWhispers\Models\Tag::class, function ($faker) {
    return [
        'confession_tag' => ucfirst($faker->word) . microtime(),
    ];
});
