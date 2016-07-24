<?php namespace App\Http\Controllers\Admin;

use App\Models\ModeratorComment as ModeratorComment;

use Illuminate\Http\Request;

class ModeratorCommentsAdminController extends AdminController {

    public function __construct()
    {
        return parent::__construct();
    }

    public function getDelete($id)
    {
        $comment = ModeratorComment::findOrFail($id);

        if (\Auth::user()->role == 'Administrator' || \Auth::user()->user_id != $comment->user_id) {
            try {
                $comment->delete();
                return \Redirect::back()->withMessage('Comment successfully deleted.')->with('alert-class', 'alert-success');
            } catch (\Exception $e) {
                return \Redirect::back()->withMessage('Error deleting comment: ' . $e->getMessage())->with('alert-class', 'alert-danger');
            }
        } else {
            return \Redirect::back()->withMessage('Only administrators and comment owners are allowed to delete moderator comments.')->with('alert-class', 'alert-danger');
        }
    }

}
