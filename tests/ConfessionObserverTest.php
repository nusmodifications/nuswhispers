<?php

use Illuminate\Support\Facades\Auth;

class ConfessionObserverTest extends TestCase
{
    public function testSaved()
    {
        $confession = factory(App\Models\Confession::class)
            ->states('pending')
            ->create();

        $user = factory(App\Models\User::class)->states('admin')->create();

        Auth::shouldReceive('check')->andReturn(true);
        Auth::shouldReceive('user')->andReturn($user);

        $confession->status = 'Approved';
        $confession->save();

        $this->seeInDatabase('confession_logs', [
            'confession_id' => $confession->getKey(),
            'status_before' => 'Pending',
            'status_after' => 'Approved',
        ]);
    }

    public function testSavedWithScheduled()
    {
        $confession = factory(App\Models\Confession::class)
            ->states('scheduled')
            ->create();

        $confession->logs()->save(
            factory(App\Models\ConfessionLog::class)->make([
                'status_before' => 'Pending',
                'status_after' => 'Scheduled',
            ])
        );

        $confession->status = 'Approved';
        $confession->save();

        $this->seeInDatabase('confession_logs', [
            'confession_id' => $confession->getKey(),
            'status_before' => 'Scheduled',
            'status_after' => 'Approved',
        ]);
    }
}
