<?php

namespace NUSWhispers\Tests\Console\Commands;

use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Event;
use NUSWhispers\Models\Confession;
use NUSWhispers\Models\User;
use NUSWhispers\Tests\TestCase;

class PostScheduledConfessionsTest extends TestCase
{
    public function testCommand()
    {
        Event::fake();

        $confession = Confession::factory()->scheduled()->create();
        $confession->queue()->create([
            'status_after' => 'rejected',
            'update_status_at' => Carbon::now()->subMinutes(5),
        ]);
        $confession->logs()->create([
            'status_before' => 'Pending',
            'status_after' => 'Scheduled',
            'changed_by_user' => User::factory()->create()->getKey(),
            'created_on' => $confession->status_updated_at,
        ]);

        Artisan::call('confession:scheduled');

        $confession = Confession::find($confession->getKey());

        $this->assertEquals('Rejected', $confession->status);
        $this->assertEquals(0, $confession->queue()->count());
    }
}
