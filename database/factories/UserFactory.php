<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use NUSWhispers\Models\User;

/** @extends Factory<User> */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        return [
            'role' => $this->faker->randomElement(['Moderator', 'Administrator']),
            'name' => $this->faker->name(),
            'email' => $this->faker->unique()->safeEmail(),
            'password' => Hash::make($this->faker->password()),
        ];
    }

    public function moderator(): static
    {
        return $this->state(['role' => 'Moderator']);
    }

    public function admin(): static
    {
        return $this->state(['role' => 'Administrator']);
    }
}
