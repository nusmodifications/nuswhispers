<?php

namespace NUSWhispers\Http\Controllers\Admin;

use NUSWhispers\Models\ModeratorComment;

class ModeratorCommentsAdminController extends AdminController
{
    public function getDelete($id)
    {
        $comment = ModeratorComment::findOrFail($id);

        if (auth()->user()->role == 'Administrator' || auth()->user()->user_id != $comment->user_id) {
            try {
                $comment->delete();

                return redirect()->back()->withMessage('Comment successfully deleted.')->with('alert-class', 'alert-success');
            } catch (\Exception $e) {
                return redirect()->back()->withMessage('Error deleting comment: ' . $e->getMessage())->with('alert-class', 'alert-danger');
            }
        } else {
            return redirect()->back()->withMessage('Only administrators and comment owners are allowed to delete moderator comments.')->with('alert-class', 'alert-danger');
        }
    }
}
