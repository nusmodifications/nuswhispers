<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Tests\TestCase;

class ImageUploadsTest extends TestCase
{
    public function testImageUploadsAreNoLongerAvailable(): void
    {
        $this->postJson('/api/images')->assertMethodNotAllowed();
    }
}
