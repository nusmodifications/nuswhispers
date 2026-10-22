<?php

namespace NUSWhispers\Tests\Listeners;

use NUSWhispers\Events\ConfessionStatusWasChanged;
use NUSWhispers\Listeners\FlushConfessionQueue;
use NUSWhispers\Tests\TestCase;

class FlushConfessionQueueTest extends TestCase
{
    protected $listener;

    public function setUp(): void
    {
        parent::setUp();
        $this->listener = new FlushConfessionQueue();
    }

    public function testHandle()
    {
        $confession = \NUSWhispers\Models\Confession::factory()->approved()->create();
        $confession->queue()->save(\NUSWhispers\Models\ConfessionQueue::factory()->make());

        $this->listener->handle(new ConfessionStatusWasChanged($confession, 'Scheduled'));

        $this->assertDatabaseMissing('confession_queue', [
            'confession_id' => $confession->getKey(),
        ]);
    }
}
