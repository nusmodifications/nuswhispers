<?php

class ModelFactoryTest extends TestCase
{
    public $modelClasses = [
        App\Models\Category::class,
        App\Models\Confession::class,
        App\Models\ConfessionLog::class,
        App\Models\ConfessionQueue::class,
        App\Models\ModeratorComment::class,
        App\Models\Tag::class,
        App\Models\User::class
    ];

    public function testRaw()
    {
        foreach ($this->modelClasses as $modelClass) {
            $model = factory($modelClass)->make();
            $this->assertInstanceOf($modelClass, $model);
        }
    }
}
