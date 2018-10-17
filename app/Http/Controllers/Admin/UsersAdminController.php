<?php

namespace NUSWhispers\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use NUSWhispers\Models\User;

class UsersAdminController extends AdminController
{
    /**
     * Create a new controller instance.
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
        return view('admin.users.add', [
            'user' => new User(),
        ]);
    }

    public function postAdd(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string',
            'password' => 'required|min:6',
            'repeat_password' => 'required|same:password',
            'role' => 'in:Moderator,Administrator',
        ]);

        try {
            $user = new User([
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'password' => Hash::make($request->input('password')),
                'role' => $request->input('role'),
            ]);
            $user->save();

            return redirect('/admin/users')->withMessage('User successfully added.')
                ->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return redirect()->back()->withMessage('Failed adding user: ' . $e->getMessage())
                ->with('alert-class', 'alert-danger');
        }
    }

    public function getEdit(User $user)
    {
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function postEdit(Request $request, User $user)
    {
        $request->validate([
            'email' => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'name' => 'required|string',
            'password' => 'min:6',
            'repeat_password' => 'same:password',
            'role' => $request->user()->user_id !== $user->user_id ? 'in:Moderator,Administrator' : '',
        ]);

        try {
            $user->update([
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'password' => Hash::make($request->input('password')),
                'role' => $request->user()->user_id !== $user->user_id ? $request->input('role') : $user->role,
            ]);

            return redirect('/admin/users')->withMessage('User successfully updated.')
                ->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return redirect()->back()->withMessage('Failed updating user: ' . $e->getMessage())
                ->with('alert-class', 'alert-danger');
        }
    }

    public function getDelete(Request $request, User $user)
    {
        if ($request->user()->getKey() === $user->getKey()) {
            return redirect()->back()->withMessage('You cannnot delete yourself!')->with('alert-class', 'alert-danger');
        }

        try {
            $user->delete();

            return redirect()->back()->withMessage('User successfully deleted.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return redirect()->back()->withMessage('Error deleting user: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }
}
