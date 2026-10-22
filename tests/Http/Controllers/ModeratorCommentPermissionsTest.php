<?php

namespace NUSWhispers\Tests\Http\Controllers;

use NUSWhispers\Models\ModeratorComment;
use NUSWhispers\Models\User;
use NUSWhispers\Tests\TestCase;

class ModeratorCommentPermissionsTest extends TestCase
{
    public function testModeratorsCannotDeleteAnotherUsersComment(): void
    {
        $moderator = User::factory()->moderator()->create();
        $comment = ModeratorComment::factory()->create();

        $this->actingAs($moderator)->get('/admin/confessions/comments/delete/' . $comment->getKey())
            ->assertRedirect();

        $this->assertModelExists($comment);
    }

    public function testModeratorsCanDeleteTheirOwnComments(): void
    {
        $moderator = User::factory()->moderator()->create();
        $comment = ModeratorComment::factory()->create(['user_id' => $moderator->getKey()]);

        $this->actingAs($moderator)->get('/admin/confessions/comments/delete/' . $comment->getKey())
            ->assertRedirect();

        $this->assertModelMissing($comment);
    }

    public function testAdministratorsCanDeleteAnotherUsersComment(): void
    {
        $administrator = User::factory()->admin()->create();
        $comment = ModeratorComment::factory()->create();

        $this->actingAs($administrator)->get('/admin/confessions/comments/delete/' . $comment->getKey())
            ->assertRedirect();

        $this->assertModelMissing($comment);
    }
}
