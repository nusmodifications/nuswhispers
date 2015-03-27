<?php namespace App\Http\Controllers\Admin;

class UsersAdminController extends AdminController {

    public function getIndex()
    {
        return view('admin.users.index');
    }

}
