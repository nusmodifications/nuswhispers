<?php

namespace NUSWhispers\Tests\Listeners;

use anlutro\LaravelSettings\Facade as Settings;
use NUSWhispers\Events\ConfessionWasCreated;
use NUSWhispers\Listeners\FilterConfessionViaWordBlacklist;
use NUSWhispers\Tests\TestCase;

class FilterConfessionViaWordBlacklistTest extends TestCase
{
    protected $listener;

    public function setUp(): void
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

        $this->assertDatabaseHas('confessions', [
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

        $this->assertDatabaseHas('confessions', [
            'confession_id' => $confession->getKey(),
            'status' => 'Pending',
        ]);
    }
}
