<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Tests\TestCase;

class ConfessionsControllerStoreTest extends TestCase
{
    public function testStoreValidationFailed()
    {
        $this->json('POST', 'api/confessions')
            ->assertStatus(422)
            ->assertJsonFragment(['success' => false]);
    }
}
