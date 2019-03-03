<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Models\Confession;
use NUSWhispers\Models\Tag;
use NUSWhispers\Tests\TestCase;

class TagsControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        cache()->clear();
    }

    public function testIndex()
    {
        $this->json('GET', '/api/tags')
            ->assertStatus(200);
    }

    public function testShow()
    {
        $tag = factory(Tag::class)->create();

        $this->json('GET', '/api/tags/' . $tag->getKey())
            ->assertJsonFragment(['confession_tag_id' => $tag->getKey()]);
    }

    public function testTopNTags()
    {
        $tag = factory(Tag::class)->create();

        factory(Confession::class)->states('approved')
            ->create()
            ->tags()
            ->sync([$tag->getKey()]);

        $this->json('GET', '/api/tags/top/5')
            ->assertJsonFragment(['confession_tag_id' => $tag->getKey()]);
    }
}
