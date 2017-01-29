<?php

namespace NUSWhispers\Tests\Listeners;

use NUSWhispers\Tests\TestCase;
use NUSWhispers\Events\ConfessionWasCreated;
use NUSWhispers\Events\ConfessionWasUpdated;
use NUSWhispers\Listeners\SyncConfessionTags;

class SyncConfessionTagsTest extends TestCase
{
    protected $listener;

    public function setUp()
    {
        parent::setUp();
        $this->listener = new SyncConfessionTags();
    }

    /** @test */
    public function testHandle()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
           'content' => 'Hello World #firstworldproblems',
        ]);

        $this->listener->handle(new ConfessionWasCreated($confession));

        $this->assertDatabaseHas('tags', [
            'confession_tag' => '#firstworldproblems',
        ]);
    }

    /** @test */
    public function testHandleDuplicateTags()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'content' => 'Hello World #firstworldproblems #firstworldproblems',
        ]);

        $this->listener->handle(new ConfessionWasCreated($confession));

        $count = \DB::table('tags')->where('confession_tag', '#firstworldproblems')->count();
        $this->assertEquals(1, $count);
    }

    /** @test */
    public function testHandleUpdateDifferentTags()
    {
        $confession = factory(\NUSWhispers\Models\Confession::class)->create([
            'content' => 'Hello World #secondworldproblems',
        ]);

        $firstTag = factory(\NUSWhispers\Models\Tag::class)->create([
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
