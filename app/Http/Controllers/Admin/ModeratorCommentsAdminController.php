<?php

namespace NUSWhispers\Http\Controllers\Admin;

use Illuminate\Http\Request;
use NUSWhispers\Models\ModeratorComment;

class ModeratorCommentsAdminController extends AdminController
{
    /**
     * Deletes a moderator comment.
     *
     * @param \Illuminate\Http\Request $request
     * @param \NUSWhispers\Models\ModeratorComment $comment
     *
     * @return mixed
     */
    public function getDelete(Request $request, ModeratorComment $comment)
    {
        if ($this->canDeleteComment($request, $comment)) {
            return $this->withErrorHandling(function () use ($comment) {
                $comment->delete();

                return $this->backWithSuccess('Comment successfully deleted.');
            });
        }

        return $this->backWithError('Only administrators and comment owners are allowed to delete moderator comments.');
    }

    /**
     * Checks whether the existing user can delete the comment.
     *
     * @param \Illuminate\Http\Request $request
     * @param \NUSWhispers\Models\ModeratorComment $comment
     *
     * @return bool
     */
    protected function canDeleteComment(Request $request, ModeratorComment $comment): bool
    {
        return $request->user()->role === 'Administrator' ||
            $request->user()->user_id !== $comment->user_id;
    }
}
