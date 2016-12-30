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
            'content' => 'Test Content'
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
        $this->assertEquals($confession->queue->count(), 1);
    }

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

        $this->assertEquals($confession->categories->count(), count($categories));
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

        $this->assertEquals($confession->categories->count(), count($categories));
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
        $this->assertEquals($confession->queue->count(), 1);
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
            \NUSWhispers\Events\ConfessionWasScheduled::class
        ]);

        $confession = factory(\NUSWhispers\Models\Confession::class)->states('pending')->create();
        $confession->queue()->save(factory(\NUSWhispers\Models\ConfessionQueue::class)->make());

        $confession = $this->service->updateStatus($confession, 'Approved', 3);

        $this->assertEquals($confession->queue->count(), 1);
        $this->assertEquals($confession->queue->first()->status_after, 'Approved');
        $this->assertEquals($confession->status, 'Scheduled');
    }
}
