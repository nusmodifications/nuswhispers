<?php

use Ramsey\Uuid\Uuid;

$factory->define(\NUSWhispers\Models\Category::class, function ($faker) {
    return [
        'confession_category' => ucfirst($faker->word) . '-' . (string) Uuid::uuid4(),
    ];
});
