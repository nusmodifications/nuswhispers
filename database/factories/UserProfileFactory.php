<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NUSWhispers\Models\User;
use NUSWhispers\Models\UserProfile;

/** @extends Factory<UserProfile> */
class UserProfileFactory extends Factory
{
    protected $model = UserProfile::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider_name' => $this->faker->unique()->word,
            'provider_id' => $this->faker->word,
        ];
    }
}
