<?php

namespace NUSWhispers\Tests\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Event;
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
        $confessions = Confession::factory(5)->create([
            'fingerprint' => 'test',
        ]);

        $this->assertEquals(5, $this->service->countByFingerprint($confessions->first()));
    }

    public function testCountByFingerprintWithStatus(): void
    {
        $confessions = Confession::factory(5)
            ->approved()
            ->create([
                'fingerprint' => 'test',
            ]);

        Confession::factory(15)
            ->rejected()
            ->create([
                'fingerprint' => 'test',
            ]);

        $this->assertEquals(5, $this->service->countByFingerprint($confessions->first(), 'Approved'));
    }

    public function testCreate(): void
    {
        Event::fake([ConfessionWasCreated::class, ConfessionStatusWasChanged::class]);

        $confession = $this->service->create([
            'content' => 'Test Content',
        ]);

        $this->assertInstanceOf(Confession::class, $confession);

        Event::assertDispatched(ConfessionWasCreated::class);
        Event::assertNotDispatched(ConfessionStatusWasChanged::class);
    }

    public function testCreateFingerprint(): void
    {
        Event::fake([ConfessionWasCreated::class]);

        $confession = $this->service->create([
            'content' => 'Test Content',
        ]);

        $this->assertNotNull($confession->fingerprint);

        Event::assertDispatched(ConfessionWasCreated::class);
    }

    public function testCreateExistingFingerprint(): void
    {
        Event::fake([ConfessionWasCreated::class]);

        $confession = $this->service->create([
            'content' => 'Test Content',
            'token' => 'abc',
        ]);

        $this->assertEquals('abc', $confession->fingerprint);

        Event::assertDispatched(ConfessionWasCreated::class);
    }

    public function testCreateScheduled(): void
    {
        Event::fake([ConfessionWasCreated::class, ConfessionWasScheduled::class, ConfessionStatusWasChanged::class]);

        $confession = $this->service->create([
            'content' => 'Hello World',
            'schedule' => 3,
            'status' => 'Approved',
        ]);

        $this->assertEquals($confession->status, 'Scheduled');
        $this->assertEquals($confession->queue()->count(), 1);

        Event::assertDispatched(ConfessionWasCreated::class);
        Event::assertDispatched(ConfessionWasScheduled::class);
        Event::assertNotDispatched(ConfessionStatusWasChanged::class);
    }

    public function testCreateWithCategories(): void
    {
        Event::fake([ConfessionWasCreated::class, ConfessionStatusWasChanged::class]);

        $categories = Category::factory(5)->create()
            ->map(static function ($item) {
                return $item->getKey();
            })
            ->all();

        $confession = $this->service->create([
            'content' => 'Test Content',
            'categories' => $categories,
        ]);

        $this->assertEquals(count($categories), $confession->categories()->count());

        Event::assertDispatched(ConfessionWasCreated::class);
        Event::assertNotDispatched(ConfessionStatusWasChanged::class);
    }

    public function testCreateCategoriesNull(): void
    {
        Event::fake();

        $confession = $this->service->create([
            'content' => 'Test Content',
            'categories' => null,
        ]);

        $this->assertEquals(0, $confession->categories()->count());
    }

    public function testDelete(): void
    {
        Event::fake([ConfessionWasDeleted::class]);

        $confession = Confession::factory()->create();
        $this->assertTrue($this->service->delete($confession->getKey()));

        Event::assertDispatched(ConfessionWasDeleted::class);
    }

    public function testFindByFingerprint(): void
    {
        $confessions = Confession::factory(5)->create([
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
        $confessions = Confession::factory(5)
            ->approved()
            ->create([
                'fingerprint' => 'test',
            ]);

        Confession::factory(15)
            ->rejected()
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
        Event::fake([ConfessionWasUpdated::class]);

        $confession = Confession::factory()->create();
        $confession = $this->service->update($confession->getKey(), ['content' => 'Part II']);

        $this->assertEquals('Part II', $confession->content);

        Event::assertDispatched(ConfessionWasUpdated::class);
    }

    public function testUpdateCategories(): void
    {
        Event::fake([ConfessionWasUpdated::class]);

        $confession = Confession::factory()->create();
        $categories = Category::factory(3)->create()
            ->map(static function ($item) {
                return $item->getKey();
            })
            ->all();

        $confession = $this->service->update($confession->getKey(), ['categories' => $categories]);

        $this->assertEquals(count($categories), $confession->categories()->count());

        Event::assertDispatched(ConfessionWasUpdated::class);
    }

    public function testUpdateNoNewCategories(): void
    {
        Event::fake([ConfessionWasUpdated::class]);

        $confession = Confession::factory()->create();
        $confession->categories()->saveMany(Category::factory(3)->create());

        $confession = $this->service->update($confession->getKey(), []);
        $this->assertEquals(3, $confession->categories()->count());

        Event::assertDispatched(ConfessionWasUpdated::class);
    }

    public function testUpdatePending(): void
    {
        $confession = Confession::factory()->approved()->create();
        $this->expectException(\InvalidArgumentException::class);
        $this->service->update($confession->getKey(), ['status' => 'Pending']);
    }

    public function testUpdatePendingSameStatus(): void
    {
        $confession = Confession::factory()->pending()->create();
        $this->service->update($confession->getKey(), ['status' => 'Pending']);

        $this->assertEquals('Pending', $confession->status);
    }

    public function testUpdateScheduled(): void
    {
        Event::fake([ConfessionStatusWasChanged::class, ConfessionWasUpdated::class, ConfessionWasScheduled::class]);

        $confession = Confession::factory()->pending()->create();
        $confession = $this->service->update($confession, [
            'schedule' => 3,
            'status' => 'Approved',
        ]);

        $this->assertEquals($confession->status, 'Scheduled');
        $this->assertEquals($confession->queue()->count(), 1);

        Event::assertDispatched(ConfessionStatusWasChanged::class);
        Event::assertDispatched(ConfessionWasUpdated::class);
        Event::assertDispatched(ConfessionWasScheduled::class);
    }

    public function testUpdateScheduledDate(): void
    {
        Event::fake([ConfessionStatusWasChanged::class, ConfessionWasUpdated::class, ConfessionWasScheduled::class]);

        $schedule = Carbon::create(2018, 1, 1);

        $confession = Confession::factory()->pending()->create();
        $confession = $this->service->update($confession, [
            'schedule' => (string) $schedule->timestamp,
            'status' => 'Approved',
        ]);

        $this->assertEquals($confession->status, 'Scheduled');
        $this->assertEquals($schedule, $confession->queue()->first()->update_status_at);

        Event::assertDispatched(ConfessionStatusWasChanged::class);
        Event::assertDispatched(ConfessionWasUpdated::class);
        Event::assertDispatched(ConfessionWasScheduled::class);
    }

    public function testUpdateStatus(): void
    {
        Event::fake([ConfessionStatusWasChanged::class, ConfessionWasApproved::class]);

        $confession = Confession::factory()->pending()->create();

        $confession = $this->service->updateStatus($confession, 'Approved');

        $this->assertEquals('Approved', $confession->status);

        Event::assertDispatched(ConfessionStatusWasChanged::class);
        Event::assertDispatched(ConfessionWasApproved::class);
    }

    public function testUpdateStatusScheduled(): void
    {
        Event::fake([ConfessionStatusWasChanged::class, ConfessionWasScheduled::class]);

        $confession = Confession::factory()->pending()->create();
        $confession->queue()->save(ConfessionQueue::factory()->make());

        $confession = $this->service->updateStatus($confession, 'Approved', 3);

        $this->assertEquals($confession->queue()->count(), 1);
        $this->assertEquals($confession->queue()->first()->status_after, 'Approved');
        $this->assertEquals($confession->status, 'Scheduled');

        Event::assertDispatched(ConfessionStatusWasChanged::class);
        Event::assertDispatched(ConfessionWasScheduled::class);
    }
}
