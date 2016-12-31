<?php

namespace NUSWhispers\Tests\Listeners;

use NUSWhispers\Tests\TestCase;
use NUSWhispers\Events\ConfessionWasCreated;
use anlutro\LaravelSettings\Facade as Settings;
use NUSWhispers\Listeners\FilterConfessionViaWordBlacklist;

class FilterConfessionViaWordBlacklistTest extends TestCase
{
    protected $listener;

    public function setUp()
    {
        parent::setUp();

        $this->listener = new FilterConfessionViaWordBlacklist();

        Settings::shouldReceive('get')->andReturn('foo, fcuk');
    }

    /** @test */
    public function testHandle()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'status' => 'Pending',
            'content' => 'Hello Fcuk.',
        ]);

        $this->listener->handle(new ConfessionWasCreated($confession));

        $this->seeInDatabase('confessions', [
            'confession_id' => $confession->getKey(),
            'status' => 'Rejected',
        ]);
    }

    /** @test */
    public function testHandleValid()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'status' => 'Pending',
            'content' => 'Hello world.',
        ]);

        $this->listener->handle(new ConfessionWasCreated($confession));

        $this->seeInDatabase('confessions', [
            'confession_id' => $confession->getKey(),
            'status' => 'Pending',
        ]);
    }
}
