<?php

namespace NUSWhispers\Tests\Listeners;

use NUSWhispers\Tests\TestCase;
use NUSWhispers\Listeners\FlushConfessionQueue;
use NUSWhispers\Events\ConfessionStatusWasChanged;

class FlushConfessionQueueTest extends TestCase
{
    protected $listener;

    public function setUp()
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

        $this->dontSeeInDatabase('confession_queue', [
            'confession_id' => $confession->getKey(),
        ]);
    }
}
