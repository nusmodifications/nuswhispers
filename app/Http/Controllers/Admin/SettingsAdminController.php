<?php

namespace NUSWhispers\Http\Controllers\Admin;

use anlutro\LaravelSettings\Facade as Settings;
use NUSWhispers\Http\Requests\AdminSettingsRequest;

class SettingsAdminController extends AdminController
{
    /**
     * Displays the settings form.
     *
     * @return mixed
     */
    public function getIndex()
    {
        return view('admin.settings.index', [
            'settings' => Settings::all(),
        ]);
    }

    /**
     * Updates the settings.
     *
     * @param \NUSWhispers\Http\Requests\AdminSettingsRequest $request
     *
     * @return mixed
     */
    public function postIndex(AdminSettingsRequest $request)
    {
        return $this->withErrorHandling(function () use ($request) {
            Settings::set($request->only([
                'word_blacklist',
                'rejection_net_score',
                'rejection_decay',
            ]));
            Settings::save();

            return $this->backWithSuccess('Settings successfully saved.');
        });
    }
}
