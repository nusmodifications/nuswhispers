<?php

namespace NUSWhispers\Tests\Models;

use NUSWhispers\Models\User;
use NUSWhispers\Models\UserProfile;
use NUSWhispers\Tests\TestCase;

class UserTest extends TestCase
{
    public function testProfiles()
    {
        $user = factory(User::class)->create();
        $user->profiles()->save(factory(UserProfile::class)->make());

        $this->assertInstanceOf(UserProfile::class, $user->profiles->first());
    }
}
