<?php

namespace NUSWhispers\Tests\Models;

use NUSWhispers\Models\Confession;
use NUSWhispers\Models\ModeratorComment;
use NUSWhispers\Models\User;
use NUSWhispers\Tests\TestCase;

class ModeratorCommentTest extends TestCase
{
    public function testConfession()
    {
        $comment = factory(ModeratorComment::class)->create();
        $this->assertInstanceOf(Confession::class, $comment->confession);
    }

    public function testUser()
    {
        $comment = factory(ModeratorComment::class)->create();
        $this->assertInstanceOf(User::class, $comment->user);
    }
}
