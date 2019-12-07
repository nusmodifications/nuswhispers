<?php

namespace NUSWhispers\Tests\Services;

use Carbon\Carbon;
use NUSWhispers\Events\ConfessionStatusWasChanged;
use NUSWhispers\Events\ConfessionWasApproved;
use NUSWhispers\Events\ConfessionWasCreated;
use NUSWhispers\Events\ConfessionWasDeleted;
use NUSWhispers\Events\ConfessionWasScheduled;
use NUSWhispers\Events\ConfessionWasUpdated;
use NUSWhispers\Models\Category;
use NUSWhispers\Models\Confession;
use NUSWhispers\Models\ConfessionQueue;
use NUSWhispers\Services\ConfessionService;
use NUSWhispers\Tests\TestCase;

class ConfessionServiceTest extends TestCase
{
    /** @var \NUSWhispers\Services\ConfessionService */
    protected $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->service = $this->app->make(ConfessionService::class);
    }

    public function testCountByFingerprint(): void
    {
        $confessions = factory(Confession::class, 5)->create([
            'fingerprint' => 'test',
        ]);

        $this->assertEquals(5, $this->service->countByFingerprint($confessions->first()));
    }

    public function testCountByFingerprintWithStatus(): void
    {
        $confessions = factory(Confession::class, 5)
            ->states(['approved'])
            ->create([
                'fingerprint' => 'test',
            ]);

        factory(Confession::class, 15)
            ->states(['rejected'])
            ->create([
                'fingerprint' => 'test',
            ]);

        $this->assertEquals(5, $this->service->countByFingerprint($confessions->first(), 'Approved'));
    }

    public function testCreate(): void
    {
        $this->expectsEvents(ConfessionWasCreated::class);
        $this->doesntExpectEvents(ConfessionStatusWasChanged::class);

        $confession = $this->service->create([
            'content' => 'Test Content',
        ]);

        $this->assertInstanceOf(Confession::class, $confession);
    }

    public function testCreateFingerprint(): void
    {
        $this->expectsEvents(ConfessionWasCreated::class);

        $confession = $this->service->create([
            'content' => 'Test Content',
        ]);

        $this->assertNotNull($confession->fingerprint);
    }

    public function testCreateExistingFingerprint(): void
    {
        $this->expectsEvents(ConfessionWasCreated::class);

        $confession = $this->service->create([
            'content' => 'Test Content',
            'token' => 'abc',
        ]);

        $this->assertEquals('abc', $confession->fingerprint);
    }

    public function testCreateScheduled(): void
    {
        $this->expectsEvents([
            ConfessionWasCreated::class,
            ConfessionWasScheduled::class,
        ]);
        $this->doesntExpectEvents(ConfessionStatusWasChanged::class);

        $confession = $this->service->create([
            'content' => 'Hello World',
            'schedule' => 3,
            'status' => 'Approved',
        ]);

        $this->assertEquals($confession->status, 'Scheduled');
        $this->assertEquals($confession->queue()->count(), 1);
    }

    public function testCreateWithCategories(): void
    {
        $this->expectsEvents(ConfessionWasCreated::class);
        $this->doesntExpectEvents(ConfessionStatusWasChanged::class);

        $categories = factory(Category::class, 5)->create()
            ->map(static function ($item) {
                return $item->getKey();
            })
            ->all();

        $confession = $this->service->create([
            'content' => 'Test Content',
            'categories' => $categories,
        ]);

        $this->assertEquals(count($categories), $confession->categories()->count());
    }

    public function testCreateCategoriesNull(): void
    {
        $this->withoutEvents();

        $confession = $this->service->create([
            'content' => 'Test Content',
            'categories' => null,
        ]);

        $this->assertEquals(0, $confession->categories()->count());
    }

    public function testDelete(): void
    {
        $this->expectsEvents(ConfessionWasDeleted::class);
        $confession = factory(Confession::class)->create();
        $this->assertTrue($this->service->delete($confession->getKey()));
    }

    public function testFindByFingerprint(): void
    {
        $confessions = factory(Confession::class, 5)->create([
            'fingerprint' => 'test',
        ]);

        $this->service
            ->findByFingerprint($confessions->first())
            ->each(function ($confession) {
                $this->assertEquals('test', $confession->fingerprint);
            });
    }

    public function testFindByFingerprintWithStatus(): void
    {
        $confessions = factory(Confession::class, 5)
            ->states(['approved'])
            ->create([
                'fingerprint' => 'test',
            ]);

        factory(Confession::class, 15)
            ->states(['rejected'])
            ->create([
                'fingerprint' => 'test',
            ]);

        $this->service
            ->findByFingerprint($confessions->first(), 'Approved')
            ->each(function ($confession) {
                $this->assertEquals('test', $confession->fingerprint);
                $this->assertEquals('Approved', $confession->status);
            });
    }

    public function testUpdate(): void
    {
        $this->expectsEvents(ConfessionWasUpdated::class);
        $confession = factory(Confession::class)->create();
        $confession = $this->service->update($confession->getKey(), ['content' => 'Part II']);

        $this->assertEquals('Part II', $confession->content);
    }

    public function testUpdateCategories(): void
    {
        $this->expectsEvents(ConfessionWasUpdated::class);
        $confession = factory(Confession::class)->create();
        $categories = factory(Category::class, 3)->create()
            ->map(static function ($item) {
                return $item->getKey();
            })
            ->all();

        $confession = $this->service->update($confession->getKey(), ['categories' => $categories]);

        $this->assertEquals(count($categories), $confession->categories()->count());
    }

    public function testUpdateNoNewCategories(): void
    {
        $this->expectsEvents(ConfessionWasUpdated::class);
        $confession = factory(Confession::class)->create();
        $confession->categories()->saveMany(factory(Category::class, 3)->create());

        $confession = $this->service->update($confession->getKey(), []);
        $this->assertEquals(3, $confession->categories()->count());
    }

    public function testUpdatePending(): void
    {
        $confession = factory(Confession::class)->states('approved')->create();
        $this->expectException(\InvalidArgumentException::class);
        $this->service->update($confession->getKey(), ['status' => 'Pending']);
    }

    public function testUpdatePendingSameStatus(): void
    {
        $confession = factory(Confession::class)->states('pending')->create();
        $this->service->update($confession->getKey(), ['status' => 'Pending']);

        $this->assertEquals('Pending', $confession->status);
    }

    public function testUpdateScheduled(): void
    {
        $this->expectsEvents([
            ConfessionStatusWasChanged::class,
            ConfessionWasUpdated::class,
            ConfessionWasScheduled::class,
        ]);

        $confession = factory(Confession::class)->states('pending')->create();
        $confession = $this->service->update($confession, [
            'schedule' => 3,
            'status' => 'Approved',
        ]);

        $this->assertEquals($confession->status, 'Scheduled');
        $this->assertEquals($confession->queue()->count(), 1);
    }

    public function testUpdateScheduledDate(): void
    {
        $this->expectsEvents([
            ConfessionStatusWasChanged::class,
            ConfessionWasUpdated::class,
            ConfessionWasScheduled::class,
        ]);

        $schedule = Carbon::create(2018, 1, 1);

        $confession = factory(Confession::class)->states('pending')->create();
        $confession = $this->service->update($confession, [
            'schedule' => (string) $schedule->timestamp,
            'status' => 'Approved',
        ]);

        $this->assertEquals($confession->status, 'Scheduled');
        $this->assertEquals($schedule, $confession->queue()->first()->update_status_at);
    }

    public function testUpdateStatus(): void
    {
        $this->expectsEvents([
            ConfessionStatusWasChanged::class,
            ConfessionWasApproved::class,
        ]);

        $confession = factory(Confession::class)->states('pending')->create();

        $confession = $this->service->updateStatus($confession, 'Approved');

        $this->assertEquals('Approved', $confession->status);
    }

    public function testUpdateStatusScheduled(): void
    {
        $this->expectsEvents([
            ConfessionStatusWasChanged::class,
            ConfessionWasScheduled::class,
        ]);

        $confession = factory(Confession::class)->states('pending')->create();
        $confession->queue()->save(factory(ConfessionQueue::class)->make());

        $confession = $this->service->updateStatus($confession, 'Approved', 3);

        $this->assertEquals($confession->queue()->count(), 1);
        $this->assertEquals($confession->queue()->first()->status_after, 'Approved');
        $this->assertEquals($confession->status, 'Scheduled');
    }
}
