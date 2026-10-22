<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Models\ModeratorComment;
use NUSWhispers\Models\User;
use NUSWhispers\Tests\TestCase;

class ModeratorCommentPermissionsTest extends TestCase
{
    public function testModeratorsCannotDeleteAnotherUsersComment(): void
    {
        $moderator = factory(User::class)->states('moderator')->create();
        $comment = factory(ModeratorComment::class)->create();

        $this->actingAs($moderator)->get('/admin/confessions/comments/delete/' . $comment->getKey())
            ->assertRedirect();

        $this->assertDatabaseHas('moderator_comments', ['comment_id' => $comment->getKey()]);
    }

    public function testModeratorsCanDeleteTheirOwnComments(): void
    {
        $moderator = factory(User::class)->states('moderator')->create();
        $comment = factory(ModeratorComment::class)->create(['user_id' => $moderator->getKey()]);

        $this->actingAs($moderator)->get('/admin/confessions/comments/delete/' . $comment->getKey())
            ->assertRedirect();

        $this->assertDatabaseMissing('moderator_comments', ['comment_id' => $comment->getKey()]);
    }

    public function testAdministratorsCanDeleteAnotherUsersComment(): void
    {
        $administrator = factory(User::class)->states('admin')->create();
        $comment = factory(ModeratorComment::class)->create();

        $this->actingAs($administrator)->get('/admin/confessions/comments/delete/' . $comment->getKey())
            ->assertRedirect();

        $this->assertDatabaseMissing('moderator_comments', ['comment_id' => $comment->getKey()]);
    }
}
