<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NUSWhispers\Models\Confession;
use NUSWhispers\Models\ConfessionQueue;

/** @extends Factory<ConfessionQueue> */
class ConfessionQueueFactory extends Factory
{
    protected $model = ConfessionQueue::class;

    public function definition(): array
    {
        return [
            'confession_id' => Confession::factory()->pending(),
            'status_after' => $this->faker->randomElement(['Approved', 'Featured', 'Rejected']),
        ];
    }
}
