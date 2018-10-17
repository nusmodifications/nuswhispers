<?php

namespace NUSWhispers\Http\Controllers\Admin;

use Illuminate\Http\Request;
use NUSWhispers\Models\ApiKey;

class ApiKeysAdminController extends AdminController
{
    public function getDelete(ApiKey $key)
    {
        try {
            $key->delete();

            return redirect()->back()->withMessage('API key successfully deleted.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return redirect()->back()->withMessage('Error deleting API key: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getIndex()
    {
        return view('admin.api-keys.index', [
            'keys' => ApiKey::orderBy('last_used_on', 'desc')->paginate(10),
        ]);
    }

    public function getAdd(Request $request)
    {
        try {
            $key = new ApiKey([
                'user_id' => (int) $request->user()->getKey(),
                'last_used_on' => new \DateTime(),
                'created_on' => new \DateTime(),
                'key' => ApiKey::generateKey(),
            ]);
            $key->save();

            return redirect('/admin/api-keys')->withMessage('API key successfully added.')
                ->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return redirect()->back()->withMessage('Failed adding API key: ' . $e->getMessage())
                ->with('alert-class', 'alert-danger');
        }
    }
}
