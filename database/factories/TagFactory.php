<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NUSWhispers\Models\Tag;

/** @extends Factory<Tag> */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition(): array
    {
        return [
            'confession_tag' => '#' . ucfirst($this->faker->word) . '-' . $this->faker->uuid,
        ];
    }
}
