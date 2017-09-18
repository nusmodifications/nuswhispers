<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Models\Confession;

class ConfessionsControllerIndexTest extends ConfessionsControllerTestCase
{
    public function testIndex()
    {
        $confession = factory(Confession::class)->states('featured')->create();

        $this->json('GET', '/api/confessions')
            ->assertJsonFragment(['confession_id' => $confession->getKey()]);
    }
}
