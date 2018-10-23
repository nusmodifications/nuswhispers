<?php

use Ramsey\Uuid\Uuid;

$factory->define(\NUSWhispers\Models\Tag::class, function ($faker) {
    return [
        'confession_tag' => '#' . ucfirst($faker->word) . '-' . (string) Uuid::uuid4(),
    ];
});
