<?php namespace App\Http\Controllers\Admin;

use App\Models\ApiKey;
use Auth;

class ApiKeysAdminController extends AdminController
{
    public function getDelete($id)
    {
        try {
            $key = ApiKey::findOrFail($id)->delete();

            return \Redirect::back()->withMessage('API key successfully deleted.')->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Error deleting API key: ' . $e->getMessage())->with('alert-class', 'alert-danger');
        }
    }

    public function getIndex()
    {
        return view('admin.api-keys.index', [
            'keys' => ApiKey::orderBy('last_used_on', 'desc')->paginate(10)
        ]);
    }

    public function getAdd()
    {
        try {
            $key = new ApiKey([
                'user_id' => (int) Auth::user()->getKey(),
                'created_on' => new \DateTime(),
                'key' => ApiKey::generateKey()
            ]);
            $key->save();
            
            return redirect('/admin/api-keys')->withMessage('API key successfully added.')
                ->with('alert-class', 'alert-success');
        } catch (\Exception $e) {
            return \Redirect::back()->withMessage('Failed adding API key: ' . $e->getMessage())
                ->with('alert-class', 'alert-danger');
        }
    }
}
