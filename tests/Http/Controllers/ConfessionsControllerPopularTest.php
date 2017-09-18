<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Models\Confession;

class ConfessionsControllerPopularTest extends ConfessionsControllerTestCase
{
    public function testPopular()
    {
        $confession = factory(Confession::class)->states('featured')->create();

        $this->json('GET', '/api/confessions/popular')
            ->assertJsonFragment(['confession_id' => $confession->getKey()]);
    }
}
