<?php

namespace NUSWhispers\Tests\Listeners;

use NUSWhispers\Events\ConfessionWasCreated;
use NUSWhispers\Events\ConfessionWasUpdated;
use NUSWhispers\Listeners\SyncConfessionTags;
use NUSWhispers\Tests\TestCase;

class SyncConfessionTagsTest extends TestCase
{
    protected $listener;

    public function setUp(): void
    {
        parent::setUp();
        $this->listener = new SyncConfessionTags();
    }

    public function testHandle()
    {
        $confession = \NUSWhispers\Models\Confession::factory()->create([
            'content' => 'Hello World #firstworldproblems',
        ]);

        $this->listener->handle(new ConfessionWasCreated($confession));

        $this->assertDatabaseHas('tags', [
            'confession_tag' => '#firstworldproblems',
        ]);
    }

    public function testHandleDuplicateTags()
    {
        $confession = \NUSWhispers\Models\Confession::factory()->create([
            'content' => 'Hello World #firstworldproblems #firstworldproblems',
        ]);

        $this->listener->handle(new ConfessionWasCreated($confession));

        $count = \DB::table('tags')->where('confession_tag', '#firstworldproblems')->count();
        $this->assertEquals(1, $count);
    }

    public function testHandleUpdateDifferentTags()
    {
        $confession = \NUSWhispers\Models\Confession::factory()->create([
            'content' => 'Hello World #secondworldproblems',
        ]);

        $firstTag = \NUSWhispers\Models\Tag::factory()->create([
            'confession_tag' => '#firstworldproblems',
        ]);

        $confession->tags()->sync([$firstTag->getKey()]);

        $this->listener->handle(new ConfessionWasUpdated($confession));

        $this->assertDatabaseHas('tags', [
            'confession_tag' => '#secondworldproblems',
        ]);

        $this->assertDatabaseMissing('confession_tags', [
            'confession_tag_id' => $firstTag->getKey(),
        ]);
    }
}
