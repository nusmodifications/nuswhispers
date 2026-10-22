<?php

namespace NUSWhispers\Tests\Models;

use NUSWhispers\Models\User;
use NUSWhispers\Models\UserProfile;
use NUSWhispers\Tests\TestCase;

class UserTest extends TestCase
{
    public function testProfiles()
    {
        $user = User::factory()->create();
        $user->profiles()->save(UserProfile::factory()->make());

        $this->assertInstanceOf(UserProfile::class, $user->profiles->first());
    }
}
