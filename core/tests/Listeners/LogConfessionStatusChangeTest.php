<?php

namespace NUSWhispers\Tests\Listeners;

use Carbon\Carbon;
use NUSWhispers\Events\ConfessionStatusWasChanged;
use NUSWhispers\Listeners\LogConfessionStatusChange;
use NUSWhispers\Tests\TestCase;

class LogConfessionStatusChangeTest extends TestCase
{
    protected $listener;

    public function setUp()
    {
        parent::setUp();
        $this->listener = new LogConfessionStatusChange();
    }

    /** @test */
    public function testHandle()
    {
        $updatedTime = Carbon::now()->toDateTimeString();

        $confession = factory(\NUSWhispers\Models\Confession::class)->states('approved')->create([
            'status_updated_at' => $updatedTime,
        ]);
        $user = factory(\NUSWhispers\Models\User::class)->create();

        $this->listener->handle(new ConfessionStatusWasChanged($confession, 'Pending', $user));

        $this->assertDatabaseHas('confession_logs', [
            'confession_id' => $confession->getKey(),
            'status_before' => 'Pending',
            'status_after' => 'Approved',
            'changed_by_user' => $user->getKey(),
            'created_on' => $updatedTime,
        ]);
    }

    /** @test */
    public function testHandleSameStatus()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->states('approved')->create();
        $this->listener->handle(new ConfessionStatusWasChanged($confession, 'Approved'));

        $this->assertDatabaseMissing('confession_logs', [
            'confession_id' => $confession->getKey(),
        ]);
    }
}
