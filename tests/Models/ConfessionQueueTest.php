<?php

namespace NUSWhispers\Tests\Models;

use NUSWhispers\Tests\TestCase;
use NUSWhispers\Models\Confession;
use NUSWhispers\Models\ConfessionQueue;

class ConfessionQueueTest extends TestCase
{
    public function testConfession()
    {
        $queue = factory(ConfessionQueue::class)->create();
        $this->assertInstanceOf(Confession::class, $queue->confession);
    }
}
