<?php

namespace NUSWhispers\Tests\Models;

use NUSWhispers\Models\Confession;
use NUSWhispers\Models\ConfessionLog;
use NUSWhispers\Models\User;
use NUSWhispers\Tests\TestCase;

class ConfessionLogTest extends TestCase
{
    public function testConfession()
    {
        $log = ConfessionLog::factory()->create();
        $this->assertInstanceOf(Confession::class, $log->confession);
    }

    public function testUser()
    {
        $log = ConfessionLog::factory()->create();
        $this->assertInstanceOf(User::class, $log->user);
    }
}
