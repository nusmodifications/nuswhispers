<?php

namespace NUSWhispers\Tests\Services;

use NUSWhispers\Tests\TestCase;
use NUSWhispers\Services\ConfessionService;

class ConfessionServiceTest extends TestCase
{
    protected $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = $this->app->make(ConfessionService::class);
    }

    /** @test */
    public function testCreate()
    {
        $this->expectsEvents(\NUSWhispers\Events\ConfessionWasCreated::class);
        $this->doesntExpectEvents(\NUSWhispers\Events\ConfessionStatusWasChanged::class);

        $confession = $this->service->create([
            'content' => 'Test Content',
        ]);

        $this->assertInstanceOf(\NUSWhispers\Models\Confession::class, $confession);
    }

    /** @test */
    public function testCreateScheduled()
    {
        $this->expectsEvents([
            \NUSWhispers\Events\ConfessionWasCreated::class,
            \NUSWhispers\Events\ConfessionWasScheduled::class,
        ]);
        $this->doesntExpectEvents(\NUSWhispers\Events\ConfessionStatusWasChanged::class);

        $confession = $this->service->create([
            'content' => 'Hello World',
            'schedule' => 3,
            'status' => 'Approved',
        ]);

        $this->assertEquals($confession->status, 'Scheduled');
        $this->assertEquals($confession->queue()->count(), 1);
    }

    /** @test */
    public function testCreateWithCategories()
    {
        $this->expectsEvents(\NUSWhispers\Events\ConfessionWasCreated::class);
        $this->doesntExpectEvents(\NUSWhispers\Events\ConfessionStatusWasChanged::class);

        $categories = factory(\NUSWhispers\Models\Category::class, 5)->create()
            ->map(function ($item) {
                return $item->getKey();
            })
            ->all();

        $confession = $this->service->create([
            'content' => 'Test Content',
            'categories' => $categories,
        ]);

        $this->assertEquals(count($categories), $confession->categories()->count());
    }

    /** @test */
    public function testCreateCategoriesNull()
    {
        $this->withoutEvents();

        $confession = $this->service->create([
            'content' => 'Test Content',
            'categories' => null,
        ]);

        $this->assertEquals(0, $confession->categories()->count());
    }

    /** @test */
    public function testDelete()
    {
        $this->expectsEvents(\NUSWhispers\Events\ConfessionWasDeleted::class);
        $confession = factory(\NUSWhispers\Models\Confession::class)->create();
        $this->assertTrue($this->service->delete($confession->getKey()));
    }

    /** @test */
    public function testUpdate()
    {
        $this->expectsEvents(\NUSWhispers\Events\ConfessionWasUpdated::class);
        $confession = factory(\NUSWhispers\Models\Confession::class)->create();
        $confession = $this->service->update($confession->getKey(), ['content' => 'Part II']);

        $this->assertEquals('Part II', $confession->content);
    }

    /** @test */
    public function testUpdateCategories()
    {
        $this->expectsEvents(\NUSWhispers\Events\ConfessionWasUpdated::class);
        $confession = factory(\NUSWhispers\Models\Confession::class)->create();
        $categories = factory(\NUSWhispers\Models\Category::class, 3)->create()
            ->map(function ($item) {
                return $item->getKey();
            })
            ->all();

        $confession = $this->service->update($confession->getKey(), ['categories' => $categories]);

        $this->assertEquals(count($categories), $confession->categories()->count());
    }

    /**
     * @test
     * @expectedException \InvalidArgumentException
     */
    public function testUpdatePending()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->states('approved')->create();
        $this->service->update($confession->getKey(), ['status' => 'Pending']);
    }

    /** @test */
    public function testUpdatePendingSameStatus()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->states('pending')->create();
        $this->service->update($confession->getKey(), ['status' => 'Pending']);

        $this->assertEquals('Pending', $confession->status);
    }

    /** @test */
    public function testUpdateScheduled()
    {
        $this->expectsEvents([
            \NUSWhispers\Events\ConfessionStatusWasChanged::class,
            \NUSWhispers\Events\ConfessionWasUpdated::class,
            \NUSWhispers\Events\ConfessionWasScheduled::class,
        ]);

        $confession = factory(\NUSWhispers\Models\Confession::class)->states('pending')->create();
        $confession = $this->service->update($confession, [
            'schedule' => 3,
            'status' => 'Approved',
        ]);

        $this->assertEquals($confession->status, 'Scheduled');
        $this->assertEquals($confession->queue()->count(), 1);
    }

    /** @test */
    public function testUpdateScheduledDate()
    {
        $this->expectsEvents([
            \NUSWhispers\Events\ConfessionStatusWasChanged::class,
            \NUSWhispers\Events\ConfessionWasUpdated::class,
            \NUSWhispers\Events\ConfessionWasScheduled::class,
        ]);

        $confession = factory(\NUSWhispers\Models\Confession::class)->states('pending')->create();
        $confession = $this->service->update($confession, [
            'schedule' => '2017-01-30T11:01:09+00:00',
            'status' => 'Approved',
        ]);

        $this->assertEquals($confession->status, 'Scheduled');
        $this->assertEquals('2017-01-30T11:01:09+00:00', $confession->queue()->first()->update_status_at->toW3cString());
    }

    /** @test */
    public function testUpdateStatus()
    {
        $this->expectsEvents([
            \NUSWhispers\Events\ConfessionStatusWasChanged::class,
            \NUSWhispers\Events\ConfessionWasApproved::class,
        ]);

        $confession = factory(\NUSWhispers\Models\Confession::class)->states('pending')->create();

        $confession = $this->service->updateStatus($confession, 'Approved');

        $this->assertEquals('Approved', $confession->status);
    }

    /** @test */
    public function testUpdateStatusScheduled()
    {
        $this->expectsEvents([
            \NUSWhispers\Events\ConfessionStatusWasChanged::class,
            \NUSWhispers\Events\ConfessionWasScheduled::class,
        ]);

        $confession = factory(\NUSWhispers\Models\Confession::class)->states('pending')->create();
        $confession->queue()->save(factory(\NUSWhispers\Models\ConfessionQueue::class)->make());

        $confession = $this->service->updateStatus($confession, 'Approved', 3);

        $this->assertEquals($confession->queue()->count(), 1);
        $this->assertEquals($confession->queue()->first()->status_after, 'Approved');
        $this->assertEquals($confession->status, 'Scheduled');
    }
}
