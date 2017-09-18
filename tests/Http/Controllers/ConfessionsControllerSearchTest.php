<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Models\Confession;

class ConfessionsControllerSearchTest extends ConfessionsControllerTestCase
{
    public function testSearch()
    {
        $confession = factory(Confession::class)->states('featured')->create([
            'content' => 'Hello world',
        ]);

        $this->json('GET', '/api/confessions/search/hello')
            ->assertJsonFragment(['confession_id' => $confession->getKey()]);
    }
}
