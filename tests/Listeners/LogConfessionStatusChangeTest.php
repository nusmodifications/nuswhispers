<?php

namespace NUSWhispers\Tests\Listeners;

use Carbon\Carbon;
use NUSWhispers\Events\ConfessionStatusWasChanged;
use NUSWhispers\Listeners\LogConfessionStatusChange;
use NUSWhispers\Tests\TestCase;

class LogConfessionStatusChangeTest extends TestCase
{
    protected $listener;

    public function setUp(): void
    {
        parent::setUp();
        $this->listener = new LogConfessionStatusChange();
    }

    public function testHandle()
    {
        $updatedTime = Carbon::now()->toDateTimeString();

        $confession = \NUSWhispers\Models\Confession::factory()->approved()->create([
            'status_updated_at' => $updatedTime,
        ]);
        $user = \NUSWhispers\Models\User::factory()->create();

        $this->listener->handle(new ConfessionStatusWasChanged($confession, 'Pending', $user));

        $this->assertDatabaseHas('confession_logs', [
            'confession_id' => $confession->getKey(),
            'status_before' => 'Pending',
            'status_after' => 'Approved',
            'changed_by_user' => $user->getKey(),
            'created_on' => $updatedTime,
        ]);
    }

    public function testHandleSameStatus()
    {
        $confession = \NUSWhispers\Models\Confession::factory()->approved()->create();
        $this->listener->handle(new ConfessionStatusWasChanged($confession, 'Approved'));

        $this->assertDatabaseMissing('confession_logs', [
            'confession_id' => $confession->getKey(),
        ]);
    }
}
