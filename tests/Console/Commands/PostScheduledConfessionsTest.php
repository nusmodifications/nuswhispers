<?php

namespace NUSWhispers\Tests\Console\Commands;

use Carbon\Carbon;
use NUSWhispers\Models\User;
use NUSWhispers\Tests\TestCase;
use NUSWhispers\Models\Confession;
use Illuminate\Support\Facades\Artisan;

class PostScheduledConfessionsTest extends TestCase
{
    public function testCommand()
    {
        $this->withoutEvents();

        $confession = factory(Confession::class)->states('scheduled')->create();
        $confession->queue()->create([
            'status_after' => 'rejected',
            'update_status_at' => Carbon::now()->subMinutes(5),
        ]);
        $confession->logs()->create([
            'status_before' => 'Pending',
            'status_after' => 'Scheduled',
            'changed_by_user' => factory(User::class)->create()->getKey(),
            'created_on' => $confession->status_updated_at,
        ]);

        Artisan::call('confession:scheduled');

        $confession = Confession::find($confession->getKey());

        $this->assertEquals('Rejected', $confession->status);
        $this->assertEquals(0, $confession->queue()->count());
    }
}
