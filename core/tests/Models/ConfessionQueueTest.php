<?php

namespace NUSWhispers\Tests\Models;

use NUSWhispers\Models\Confession;
use NUSWhispers\Models\ConfessionQueue;
use NUSWhispers\Tests\TestCase;

class ConfessionQueueTest extends TestCase
{
    public function testConfession()
    {
        $queue = factory(ConfessionQueue::class)->create();
        $this->assertInstanceOf(Confession::class, $queue->confession);
    }
}
