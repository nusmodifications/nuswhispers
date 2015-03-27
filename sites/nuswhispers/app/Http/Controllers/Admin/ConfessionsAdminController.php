<?php namespace App\Http\Controllers\Admin;

class ConfessionsAdminController extends AdminController {

    public function getIndex()
    {
        return view('admin.confessions.index');
    }

}
