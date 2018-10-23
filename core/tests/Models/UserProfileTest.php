<?php

namespace NUSWhispers\Tests\Models;

use NUSWhispers\Models\User;
use NUSWhispers\Models\UserProfile;
use NUSWhispers\Tests\TestCase;

class UserProfileTest extends TestCase
{
    public function testUser()
    {
        $profile = factory(UserProfile::class)->create();
        $this->assertInstanceOf(User::class, $profile->user);
    }
}
