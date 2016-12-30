<?php

namespace NUSWhispers\Tests;

class ModelFactoryTest extends TestCase
{
    public function testRaw()
    {
        $modelClasses = [
            \NUSWhispers\Models\Category::class,
            \NUSWhispers\Models\Confession::class,
            \NUSWhispers\Models\ConfessionLog::class,
            \NUSWhispers\Models\ConfessionQueue::class,
            \NUSWhispers\Models\ModeratorComment::class,
            \NUSWhispers\Models\Tag::class,
            \NUSWhispers\Models\User::class,
        ];

        foreach ($modelClasses as $modelClass) {
            $model = factory($modelClass)->make();
            $this->assertInstanceOf($modelClass, $model);
        }
    }
}
