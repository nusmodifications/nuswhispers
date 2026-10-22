<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NUSWhispers\Models\Confession;

/** @extends Factory<Confession> */
class ConfessionFactory extends Factory
{
    protected $model = Confession::class;

    public function definition(): array
    {
        return [
            'content' => $this->faker->text(),
            'images' => $this->faker->imageUrl(),
            'status' => $this->faker->randomElement(Confession::statuses()),
            'views' => $this->faker->randomNumber(4),
            'fb_like_count' => $this->faker->randomNumber(3),
            'fb_comment_count' => $this->faker->randomNumber(2),
            'fingerprint' => $this->faker->uuid,
        ];
    }

    public function featured(): static
    {
        return $this->state(['status' => 'Featured']);
    }

    public function scheduled(): static
    {
        return $this->state(['status' => 'Scheduled']);
    }

    public function pending(): static
    {
        return $this->state(['status' => 'Pending']);
    }

    public function approved(): static
    {
        return $this->state(['status' => 'Approved']);
    }

    public function rejected(): static
    {
        return $this->state(['status' => 'Rejected']);
    }
}
