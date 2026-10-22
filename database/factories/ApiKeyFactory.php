<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NUSWhispers\Models\ApiKey;
use NUSWhispers\Models\User;

/** @extends Factory<ApiKey> */
class ApiKeyFactory extends Factory
{
    protected $model = ApiKey::class;

    public function definition(): array
    {
        return [
            'key' => $this->faker->word,
            'user_id' => User::factory(),
        ];
    }
}
