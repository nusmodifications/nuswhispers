<?php

namespace NUSWhispers\Http\Controllers\Admin;

use NUSWhispers\Models\User;

class UsersAdminController extends AdminController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('adminAuth');
    }

    public function getIndex()
    {
        return view('admin.users.index', [
            'users' => User::orderBy('created_at', 'desc')->paginate(10),
        ]);
    }

    public function getAdd()
    {
        $user = new User();

        return view('admin.users.add', [
            'user' => $user,
        ]);
    }

    public function postAdd()
    {
        $validationRules = [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string',
            'password' => 'required|min:6',
            'repeat_password' => 'required|same:password',
            'role' => 'in:Moderator,Administrator',
        ];

        $validator = \Validator::make(\Input::all(), $validationRules);
        if ($validator->fails()) {
            return \Redirect::back()->withInput()->withErrors($validator);
        }

        try {
            $user = new User([
                'email' => \Input::get('email'),
                'name' => \Input::get('name'),
                'password' => \Hash::make(\Input::get('password')),
                'role' => \Input::get('role'),
            ]);
            $user->save();

            return redirect('/admin/users')->withMessage('User successfully added.')
                ->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Failed adding user: ' . $e->getMessage())
                ->with('alert-class', 'alert-danger');
        }
    }

    public function getEdit($id)
    {
        $user = User::findOrFail($id);

        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function postEdit($id)
    {
        $user = User::findOrFail($id);

        $validationRules = [
            'email' => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'name' => 'required|string',
            'password' => 'min:6',
            'repeat_password' => 'same:password',
        ];

        if (\Auth::user()->user_id != $user->user_id) {
            $validationRules['role'] = 'in:Moderator,Administrator';
        }

        $validator = \Validator::make(\Input::all(), $validationRules);
        if ($validator->fails()) {
            return \Redirect::back()->withInput()->withErrors($validator);
        }

        try {
            $user->update([
                'email' => \Input::get('email'),
                'name' => \Input::get('name'),
                'password' => \Hash::make(\Input::get('password')),
                'role' => \Auth::user()->user_id != $user->user_id ? \Input::get('role') : $user->role,
            ]);

            return redirect('/admin/users')->withMessage('User successfully updated.')
                ->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Failed updating user: ' . $e->getMessage())
                ->with('alert-class', 'alert-danger');
        }
    }

    public function getDelete($id)
    {
        if (\Auth::user()->user_id == $id) {
            return \Redirect::back()->withMessage('You cannnot delete yourself!')->with('alert-class', 'alert-danger');
        }

        $user = User::findOrFail($id);

        try {
            $user->delete();

            return \Redirect::back()->withMessage('User successfully deleted.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error deleting user: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }
}
