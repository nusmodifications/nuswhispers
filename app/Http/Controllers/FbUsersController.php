<?php

namespace NUSWhispers\Http\Controllers;

use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use NUSWhispers\Models\Confession;
use NUSWhispers\Models\FbUser;

class FbUsersController extends Controller
{
    /**
     * @var \Facebook\Facebook
     */
    protected $fb;

    /**
     * Constructs an instance of the controller.
     *
     * @param \Facebook\Facebook $fb
     */
    public function __construct(Facebook $fb)
    {
        $this->fb = $fb;
    }

    // use Facebook access token to login user
    public function postLogin()
    {
        if (request()->input('fb_access_token')) {
            $accessToken = request()->input('fb_access_token');
            try {
                $response = $this->fb->get('/me?fields=id', $accessToken);
                $fbUserId = $response->getGraphUser()->getField('id');
                $fbUser = FbUser::firstOrNew(['fb_user_id' => $fbUserId]);

                if ($fbUser->save()) {
                    session()->put('fb_user_id', $fbUserId);

                    return response()->json(['success' => true]);
                }
            } catch (FacebookSDKException $e) {
                return response()->json(['success' => false, 'errors' => [$e->getMessage()]]);
            }
        }

        return response()->json(['success' => false]);
    }

    public function postLogout()
    {
        session()->forget('fb_user_id');

        return response()->json(['success' => true]);
    }

    // add a confession to user's favourites
    public function postFavourite()
    {
        $fbUserId = session()->get('fb_user_id');
        $confessionId = request()->input('confession_id');

        if ($fbUserId && $confessionId) {
            $fbUser = FbUser::find($fbUserId);
            if (! Confession::find($confessionId)) {
                return response()->json(['success' => false, 'errors' => ['Confession does not exist.']]);
            }
            if ($fbUser->favourites->contains($confessionId)) {
                return response()->json(['success' => false, 'errors' => ['Confession already favourited.']]);
            }

            $fbUser->favourites()->attach($confessionId);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'errors' => ['User not logged in.']]);
    }

    public function postUnfavourite()
    {
        $fbUserId = session()->get('fb_user_id');
        $confessionId = request()->input('confession_id');

        if ($fbUserId && $confessionId) {
            $fbUser = FbUser::find($fbUserId);

            $fbUser->favourites()->detach($confessionId);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'errors' => ['User not logged in.']]);
    }
}
