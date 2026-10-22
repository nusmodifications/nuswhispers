<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NUSWhispers\Models\Confession;
use NUSWhispers\Models\ConfessionLog;
use NUSWhispers\Models\User;

/** @extends Factory<ConfessionLog> */
class ConfessionLogFactory extends Factory
{
    protected $model = ConfessionLog::class;

    public function definition(): array
    {
        $statusBefore = $this->faker->randomElement(Confession::statuses());
        $statusAfter = $this->faker->randomElement(array_values(array_diff(Confession::statuses(), [$statusBefore])));

        return [
            'confession_id' => Confession::factory()->state(['status' => $statusAfter]),
            'changed_by_user' => User::factory(),
            'status_before' => $statusBefore,
            'status_after' => $statusAfter,
        ];
    }
}
