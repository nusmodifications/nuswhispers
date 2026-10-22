<?php

namespace NUSWhispers\Tests\Listeners;

use anlutro\LaravelSettings\Facade as Settings;
use Carbon\Carbon;
use Mockery;
use NUSWhispers\Events\ConfessionWasCreated;
use NUSWhispers\Listeners\FilterConfessionViaFingerprint;
use NUSWhispers\Tests\TestCase;

class FilterConfessionViaFingerprintTest extends TestCase
{
    protected $listener;

    public function setUp(): void
    {
        parent::setUp();

        $this->listener = new FilterConfessionViaFingerprint();

        Settings::shouldReceive('get')
            ->with('rejection_net_score', Mockery::any())
            ->andReturn(5);

        Settings::shouldReceive('get')
            ->with('rejection_decay', Mockery::any())
            ->andReturn(30);
    }

    public function testHandle()
    {
        \NUSWhispers\Models\Confession::factory(10)->create([
            'status' => 'Rejected',
            'fingerprint' => 'foo',
        ]);

        $confession = \NUSWhispers\Models\Confession::factory()->create([
            'status' => 'Pending',
            'fingerprint' => 'foo',
        ]);

        $this->listener->handle(new ConfessionWasCreated($confession));

        $this->assertDatabaseHas('confessions', [
            'confession_id' => $confession->getKey(),
            'status' => 'Rejected',
        ]);
    }

    public function testHandleDisabled()
    {
        // Calling the same expectation should override the previous one.
        Settings::shouldReceive('get')
            ->with('rejection_net_score', Mockery::any())
            ->andReturn(0);

        Settings::shouldReceive('get')
            ->with('rejection_decay', Mockery::any())
            ->andReturn(null);

        \NUSWhispers\Models\Confession::factory(10)->create([
            'status' => 'Rejected',
            'fingerprint' => 'foo',
        ]);

        $confession = \NUSWhispers\Models\Confession::factory()->create([
            'status' => 'Pending',
            'fingerprint' => 'foo',
        ]);

        $this->listener->handle(new ConfessionWasCreated($confession));

        $this->assertDatabaseHas('confessions', [
            'confession_id' => $confession->getKey(),
            'status' => 'Rejected',
        ]);
    }

    public function testHandleNewFingerprint()
    {
        $confession = \NUSWhispers\Models\Confession::factory()->create([
            'status' => 'Pending',
            'fingerprint' => 'foo',
        ]);

        $this->listener->handle(new ConfessionWasCreated($confession));

        $this->assertDatabaseHas('confessions', [
            'confession_id' => $confession->getKey(),
            'status' => 'Pending',
        ]);
    }

    public function testHandleNetScore()
    {
        \NUSWhispers\Models\Confession::factory(10)->create([
            'status' => 'Rejected',
            'fingerprint' => 'foo',
        ]);

        \NUSWhispers\Models\Confession::factory(10)->create([
            'status' => 'Approved',
            'fingerprint' => 'foo',
        ]);

        $confession = \NUSWhispers\Models\Confession::factory()->create([
            'status' => 'Pending',
            'fingerprint' => 'foo',
        ]);

        $this->listener->handle(new ConfessionWasCreated($confession));

        $this->assertDatabaseHas('confessions', [
            'confession_id' => $confession->getKey(),
            'status' => 'Pending',
        ]);
    }

    public function testHandleExpired()
    {
        \NUSWhispers\Models\Confession::factory(10)->create([
            'status' => 'Rejected',
            'fingerprint' => 'foo',
            'created_at' => Carbon::now()->subDays(30)->toDateTimeString(),
        ]);

        $confession = \NUSWhispers\Models\Confession::factory()->create([
            'status' => 'Pending',
            'fingerprint' => 'foo',
        ]);

        $this->listener->handle(new ConfessionWasCreated($confession));

        $this->assertDatabaseHas('confessions', [
            'confession_id' => $confession->getKey(),
            'status' => 'Pending',
        ]);
    }
}
