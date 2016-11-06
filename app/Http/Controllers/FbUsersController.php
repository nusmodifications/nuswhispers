<?php

namespace App\Http\Controllers;

use App\Models\Confession as Confession;
use App\Models\FbUser as FbUser;

class FbUsersController extends Controller
{
    // use Facebook access token to login user
    public function postLogin()
    {
        if (\Input::get('fb_access_token')) {
            $accessToken = \Input::get('fb_access_token');
            try {
                $response = \Facebook::get('/me?fields=id', $accessToken);
                $fbUserId = $response->getGraphUser()->getProperty('id');
                $fbUser = FbUser::firstOrNew(['fb_user_id' => $fbUserId]);

                if ($fbUser->save()) {
                    \Session::put('fb_user_id', $fbUserId);

                    return \Response::json(['success' => true]);
                }
            } catch (\Facebook\Exceptions\FacebookSDKException $e) {
                return \Response::json(['success' => false, 'errors' => [$e->getMessage()]]);
            }
        }

        return \Response::json(['success' => false]);
    }

    public function postLogout()
    {
        \Session::forget('fb_user_id');

        return \Response::json(['success' => true]);
    }

    // add a confession to user's favourites
    public function postFavourite()
    {
        $fbUserId = \Session::get('fb_user_id');
        $confessionId = \Input::get('confession_id');

        if ($fbUserId && $confessionId) {
            $fbUser = FbUser::find($fbUserId);
            if (!Confession::find($confessionId)) {
                return \Response::json(['success' => false, 'errors' => ['Confession does not exist.']]);
            }
            if ($fbUser->favourites->contains($confessionId)) {
                return \Response::json(['success' => false, 'errors' => ['Confession already favourited.']]);
            }

            $fbUser->favourites()->attach($confessionId);

            return \Response::json(['success' => true]);
        }

        return \Response::json(['success' => false, 'errors' => ['User not logged in.']]);
    }

    public function postUnfavourite()
    {
        $fbUserId = \Session::get('fb_user_id');
        $confessionId = \Input::get('confession_id');

        if ($fbUserId && $confessionId) {
            $fbUser = FbUser::find($fbUserId);

            $fbUser->favourites()->detach($confessionId);

            return \Response::json(['success' => true]);
        }

        return \Response::json(['success' => false, 'errors' => ['User not logged in.']]);
    }
}
