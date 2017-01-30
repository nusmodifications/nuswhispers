<?php

namespace NUSWhispers\Tests\Models;

use NUSWhispers\Tests\TestCase;
use NUSWhispers\Models\Confession;
use NUSWhispers\Models\ConfessionLog;
use NUSWhispers\Models\ConfessionQueue;
use NUSWhispers\Models\ModeratorComment;

class ConfessionTest extends TestCase
{
    protected $confession;

    public function setUp()
    {
        parent::setUp();
        $this->confession = factory(Confession::class)->create();
    }

    public function testLogs()
    {
        $this->confession->logs()->save(factory(ConfessionLog::class)->make());
        $this->assertInstanceOf(ConfessionLog::class, $this->confession->logs->first());
    }

    public function testModeratorComments()
    {
        $this->confession->moderatorComments()->save(factory(ModeratorComment::class)->make());
        $this->assertInstanceOf(ModeratorComment::class, $this->confession->moderatorComments->first());
    }

    public function testQueue()
    {
        $this->confession->queue()->save(factory(ConfessionQueue::class)->make());
        $this->assertInstanceOf(ConfessionQueue::class, $this->confession->queue->first());
    }
}
