<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Models\Confession;

class ConfessionsControllerShowTest extends ConfessionsControllerTestCase
{
    public function testShow()
    {
        $confession = Confession::factory()->featured()->create();

        $this->json('GET', '/api/confessions/' . $confession->getKey())
            ->assertJsonFragment(['confession_id' => $confession->getKey()]);
    }
}
