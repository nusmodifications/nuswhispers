<?php

namespace NUSWhispers\Tests\Services;

use Carbon\Carbon;
use NUSWhispers\Services\ConfessionService;
use NUSWhispers\Tests\TestCase;

class ConfessionServiceTest extends TestCase
{
    /** @var \NUSWhispers\Services\ConfessionService */
    protected $service;

    public function setUp()
    {
        parent::setUp();
        $this->service = $this->app->make(ConfessionService::class);
    }

    public function testCountByFingerprint()
    {
        $confessions = factory(\NUSWhispers\Models\Confession::class, 5)->create([
            'fingerprint' => 'test',
        ]);

        $this->assertEquals(5, $this->service->countByFingerprint($confessions->first()));
    }

    public function testCountByFingerprintWithStatus()
    {
        $confessions = factory(\NUSWhispers\Models\Confession::class, 5)
            ->states(['approved'])
            ->create([
                'fingerprint' => 'test',
            ]);

        factory(\NUSWhispers\Models\Confession::class, 15)
            ->states(['rejected'])
            ->create([
                'fingerprint' => 'test',
            ]);

        $this->assertEquals(5, $this->service->countByFingerprint($confessions->first(), 'Approved'));
    }

    public function testCreate()
    {
        $this->expectsEvents(\NUSWhispers\Events\ConfessionWasCreated::class);
        $this->doesntExpectEvents(\NUSWhispers\Events\ConfessionStatusWasChanged::class);

        $confession = $this->service->create([
            'content' => 'Test Content',
        ]);

        $this->assertInstanceOf(\NUSWhispers\Models\Confession::class, $confession);
    }

    public function testCreateFingerprint()
    {
        $this->expectsEvents(\NUSWhispers\Events\ConfessionWasCreated::class);

        $confession = $this->service->create([
            'content' => 'Test Content',
        ]);

        $this->assertNotNull($confession->fingerprint);
    }

    public function testCreateExistingFingerprint()
    {
        $this->expectsEvents(\NUSWhispers\Events\ConfessionWasCreated::class);

        $confession = $this->service->create([
            'content' => 'Test Content',
            'token' => 'abc',
        ]);

        $this->assertEquals('abc', $confession->fingerprint);
    }

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

    public function testCreateCategoriesNull()
    {
        $this->withoutEvents();

        $confession = $this->service->create([
            'content' => 'Test Content',
            'categories' => null,
        ]);

        $this->assertEquals(0, $confession->categories()->count());
    }

    public function testDelete()
    {
        $this->expectsEvents(\NUSWhispers\Events\ConfessionWasDeleted::class);
        $confession = factory(\NUSWhispers\Models\Confession::class)->create();
        $this->assertTrue($this->service->delete($confession->getKey()));
    }

    public function testFindByFingerprint()
    {
        $confessions = factory(\NUSWhispers\Models\Confession::class, 5)->create([
            'fingerprint' => 'test',
        ]);

        $this->service
            ->findByFingerprint($confessions->first())
            ->each(function ($confession) use ($confessions) {
                $this->assertEquals('test', $confession->fingerprint);
            });
    }

    public function testFindByFingerprintWithStatus()
    {
        $confessions = factory(\NUSWhispers\Models\Confession::class, 5)
            ->states(['approved'])
            ->create([
                'fingerprint' => 'test',
            ]);

        factory(\NUSWhispers\Models\Confession::class, 15)
            ->states(['rejected'])
            ->create([
                'fingerprint' => 'test',
            ]);

        $this->service
            ->findByFingerprint($confessions->first(), 'Approved')
            ->each(function ($confession) use ($confessions) {
                $this->assertEquals('test', $confession->fingerprint);
                $this->assertEquals('Approved', $confession->status);
            });
    }

    public function testUpdate()
    {
        $this->expectsEvents(\NUSWhispers\Events\ConfessionWasUpdated::class);
        $confession = factory(\NUSWhispers\Models\Confession::class)->create();
        $confession = $this->service->update($confession->getKey(), ['content' => 'Part II']);

        $this->assertEquals('Part II', $confession->content);
    }

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

    public function testUpdateNoNewCategories()
    {
        $this->expectsEvents(\NUSWhispers\Events\ConfessionWasUpdated::class);
        $confession = factory(\NUSWhispers\Models\Confession::class)->create();
        $confession->categories()->saveMany(factory(\NUSWhispers\Models\Category::class, 3)->create());

        $confession = $this->service->update($confession->getKey(), []);
        $this->assertEquals(3, $confession->categories()->count());
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUpdatePending()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->states('approved')->create();
        $this->service->update($confession->getKey(), ['status' => 'Pending']);
    }

    public function testUpdatePendingSameStatus()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->states('pending')->create();
        $this->service->update($confession->getKey(), ['status' => 'Pending']);

        $this->assertEquals('Pending', $confession->status);
    }

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

    public function testUpdateScheduledDate()
    {
        $this->expectsEvents([
            \NUSWhispers\Events\ConfessionStatusWasChanged::class,
            \NUSWhispers\Events\ConfessionWasUpdated::class,
            \NUSWhispers\Events\ConfessionWasScheduled::class,
        ]);

        $schedule = Carbon::create(2018, 1, 1);

        $confession = factory(\NUSWhispers\Models\Confession::class)->states('pending')->create();
        $confession = $this->service->update($confession, [
            'schedule' => (string) $schedule->timestamp,
            'status' => 'Approved',
        ]);

        $this->assertEquals($confession->status, 'Scheduled');
        $this->assertEquals($schedule, $confession->queue()->first()->update_status_at);
    }

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
