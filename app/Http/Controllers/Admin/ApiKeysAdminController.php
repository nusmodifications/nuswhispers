<?php

namespace NUSWhispers\Http\Controllers\Admin;

use Illuminate\Http\Request;
use NUSWhispers\Models\ApiKey;

class ApiKeysAdminController extends AdminController
{
    /**
     * Deletes an API key.
     *
     * @param \NUSWhispers\Models\ApiKey $key
     *
     * @return mixed
     */
    public function getDelete(ApiKey $key)
    {
        return $this->withErrorHandling(function () use ($key) {
            $key->delete();

            return $this->backWithSuccess("API key '$key' successfully deleted.");
        });
    }

    /**
     * Manage API keys page.
     *
     * @return mixed
     */
    public function getIndex()
    {
        return view('admin.api-keys.index', [
            'keys' => ApiKey::query()->orderBy('last_used_on', 'desc')->paginate(10),
        ]);
    }

    /**
     * Adds a new API key.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function getAdd(Request $request)
    {
        return $this->withErrorHandling(function () use ($request) {
            (new ApiKey([
                'user_id' => (int) $request->user()->getKey(),
                'last_used_on' => new \DateTime(),
                'created_on' => new \DateTime(),
                'key' => ApiKey::generateKey(),
            ]))->save();

            return $this->backWithSuccess('API key successfully added.');
        });
    }
}
