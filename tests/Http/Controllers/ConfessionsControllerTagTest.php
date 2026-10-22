<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Models\Confession;
use NUSWhispers\Models\Tag;

class ConfessionsControllerTagTest extends ConfessionsControllerTestCase
{
    public function testTag()
    {
        $tag = Tag::factory()->create();

        $confession = Confession::factory()->approved()->create();
        $confession->tags()->sync([$tag->getKey()]);

        $this->json('GET', '/api/confessions/tag/' . substr($tag->confession_tag, 1))
            ->assertJsonFragment(['confession_id' => $confession->getKey()]);
    }
}
