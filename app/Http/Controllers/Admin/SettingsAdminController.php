<?php

namespace NUSWhispers\Http\Controllers\Admin;

use anlutro\LaravelSettings\Facade as Settings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class SettingsAdminController extends AdminController
{
    /**
     * Create a new controller instance.
     *
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

    public function postIndex(Request $request)
    {
        Settings::set('word_blacklist', $request->input('word_blacklist', ''));
        Settings::save();

        return Redirect::back()->withMessage('Settings successfully saved.')
            ->with('alert-class', 'alert-success');
    }
}
