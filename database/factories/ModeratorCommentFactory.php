<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use NUSWhispers\Models\Confession;
use NUSWhispers\Models\ModeratorComment;
use NUSWhispers\Models\User;

/** @extends Factory<ModeratorComment> */
class ModeratorCommentFactory extends Factory
{
    protected $model = ModeratorComment::class;

    public function definition(): array
    {
        return [
            'confession_id' => Confession::factory(),
            'user_id' => User::factory(),
            'content' => $this->faker->text(),
        ];
    }
}
