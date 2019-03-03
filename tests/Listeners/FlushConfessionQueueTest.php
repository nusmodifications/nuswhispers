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

    /** @test */
    public function testHandle()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->states('approved')->create();
        $confession->queue()->save(factory(\NUSWhispers\Models\ConfessionQueue::class)->make());

        $this->listener->handle(new ConfessionStatusWasChanged($confession, 'Scheduled'));

        $this->assertDatabaseMissing('confession_queue', [
            'confession_id' => $confession->getKey(),
        ]);
    }
}
