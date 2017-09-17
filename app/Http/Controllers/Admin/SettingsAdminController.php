<?php

namespace NUSWhispers\Http\Controllers\Admin;

use anlutro\LaravelSettings\Facade as Settings;
use Illuminate\Support\Facades\Redirect;
use NUSWhispers\Http\Requests\AdminSettingsRequest;

class SettingsAdminController extends AdminController
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
        return view('admin.settings.index', [
            'settings' => Settings::all(),
        ]);
    }

    public function postIndex(AdminSettingsRequest $request)
    {
        Settings::set($request->only([
            'word_blacklist',
            'rejection_net_score',
            'rejection_decay',
        ]));
        Settings::save();

        return redirect()->back()->withMessage('Settings successfully saved.')
            ->with('alert-class', 'alert-success');
    }
}
