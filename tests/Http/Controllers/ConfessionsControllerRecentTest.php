<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Models\Confession;

class ConfessionsControllerRecentTest extends ConfessionsControllerTestCase
{
    public function testRecent()
    {
        $confession = Confession::factory()->approved()->create();

        $this->json('GET', '/api/confessions/recent')
            ->assertJsonFragment(['confession_id' => $confession->getKey()]);
    }
}
