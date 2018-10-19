<?php

namespace NUSWhispers\Http\Controllers\Admin;

use Illuminate\Http\Request;
use NUSWhispers\Models\ModeratorComment;

class ModeratorCommentsAdminController extends AdminController
{
    public function getDelete(Request $request, ModeratorComment $comment)
    {
        if ($request->user()->role === 'Administrator' || $request->user()->user_id !== $comment->user_id) {
            try {
                $comment->delete();

                return redirect()->back()->withMessage('Comment successfully deleted.')->with('alert-class', 'alert-success');
            } catch (\Exception $e) {
                return redirect()->back()->withMessage('Error deleting comment: ' . $e->getMessage())->with('alert-class', 'alert-danger');
            }
        }

        return redirect()->back()->withMessage('Only administrators and comment owners are allowed to delete moderator comments.')->with('alert-class', 'alert-danger');
    }
}
