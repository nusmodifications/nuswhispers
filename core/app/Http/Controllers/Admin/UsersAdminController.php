<?php

namespace NUSWhispers\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use NUSWhispers\Models\User;

class UsersAdminController extends AdminController
{
    /**
     * List all users.
     *
     * @return mixed
     */
    public function getIndex()
    {
        return view('admin.users.index', [
            'users' => User::query()->orderBy('created_at', 'desc')->paginate(10),
        ]);
    }

    /**
     * Displays new user form.
     *
     * @return mixed
     */
    public function getAdd()
    {
        return view('admin.users.add', [
            'user' => new User(),
        ]);
    }

    /**
     * Creates a new user.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postAdd(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users,email',
            'name' => 'required|string',
            'password' => 'required|min:6',
            'repeat_password' => 'required|same:password',
            'role' => 'in:Moderator,Administrator',
        ]);

        return $this->withErrorHandling(function () use ($request) {
            (new User([
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'password' => Hash::make($request->input('password')),
                'role' => $request->input('role'),
            ]))->save();

            return redirect('admin/users')
                ->with('message', 'User successfully added.')
                ->with('alert-class', 'alert-success');
        });
    }

    /**
     * Displays the edit form.
     *
     * @param \NUSWhispers\Models\User $user
     *
     * @return mixed
     */
    public function getEdit(User $user)
    {
        return view('admin.users.edit', ['user' => $user]);
    }

    /**
     * Updates a user.
     *
     * @param \Illuminate\Http\Request $request
     * @param \NUSWhispers\Models\User $user
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postEdit(Request $request, User $user)
    {
        $this->validate($request, [
            'email' => 'required|email|unique:users,email,' . $user->user_id . ',user_id',
            'name' => 'required|string',
            'password' => 'min:6',
            'repeat_password' => 'same:password',
            'role' => $request->user()->user_id !== $user->user_id ? 'in:Moderator,Administrator' : '',
        ]);

        $this->withErrorHandling(function () use ($user, $request) {
            $user->update([
                'email' => $request->input('email'),
                'name' => $request->input('name'),
                'password' => Hash::make($request->input('password')),
                'role' => $request->user()->user_id !== $user->user_id ? $request->input('role') : $user->role,
            ]);

            return redirect('admin/users')
                ->with('message', 'User successfully updated.')
                ->with('alert-class', 'alert-success');
        });
    }

    /**
     * Deletes a user.
     *
     * @param \NUSWhispers\Models\User $user
     *
     * @return mixed
     */
    public function getDelete(User $user)
    {
        if (Gate::denies($user, 'delete')) {
            return $this->backWithError('You cannnot delete yourself!');
        }

        return $this->withErrorHandling(function () use ($user) {
            $user->delete();

            return $this->backWithSuccess('User successfully deleted.');
        });
    }
}
