<?php

namespace NUSWhispers\Http\Controllers\Admin;

use Facebook\Exceptions\FacebookResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Laravel\Socialite\Facades\Socialite;
use NUSWhispers\Models\UserProfile;
use RuntimeException;
use SammyK\LaravelFacebookSdk\FacebookFacade as Facebook;

class ProfileController extends AdminController
{
    /**
     * Displays the current user's profile page.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function getIndex(Request $request)
    {
        $allowedProviders = ['facebook' => 'Facebook'];

        return view('admin.profile.index', [
            'providers' => $allowedProviders,
            'user' => $request->user(),
            'profiles' => $request->user()->profiles->keyBy('provider_name'),
        ]);
    }

    /**
     * Edits the current user's profile.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     * @throws \Illuminate\Validation\ValidationException
     */
    public function postEdit(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'name' => 'required|string',
            'new_password' => 'min:6|max:32|string',
            'repeat_password' => 'same:new_password',
        ]);

        return $this->withErrorHandling(function () use ($request) {
            $request->user()->update([
                'email' => request()->input('email'),
                'name' => request()->input('name'),
                'password' => request()->input('new_password') ? Hash::make(request()->input('new_password')) : $request->user()->password,
            ]);

            return $this->backWithSuccess('Profile succesfully updated.');
        });
    }

    /**
     * Connects the existing user to a provider.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $provider
     *
     * @return mixed
     */
    public function getConnect(Request $request, $provider = 'facebook')
    {
        if ($request->all()) {
            try {
                $this->addProfile($provider, $request->user(), Socialite::with($provider)->user());
                $this->flashMessage('Sucessfully connected to ' . ucfirst($provider) . '.');
            } catch (\Exception $e) {
                $this->flashMessage('Error connecting to provider: ' . $e->getMessage(), 'alert-danger');
            }

            return redirect('/admin/profile');
        }

        $scopes = $provider === 'facebook' ? ['manage_pages', 'publish_pages'] : [];

        return Socialite::with($provider)->scopes($scopes)->redirect();
    }

    /**
     * Un-connects the user to a provider.
     *
     * @param \Illuminate\Http\Request $request
     * @param string $provider
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function getDelete(Request $request, $provider = 'facebook')
    {
        UserProfile::query()
            ->where('user_id', '=', $request->user()->user_id)
            ->where('provider_name', '=', $provider)
            ->delete();

        $this->flashMessage('Sucessfully removed ' . ucfirst($provider) . '.');

        return redirect('/admin/profile');
    }

    /**
     * Adds a provider to the profile.
     *
     * TODO: Refactor to somewehere better; somewhere like a UserRepository.
     *
     * @param string $provider provider name
     * @param \NUSWhispers\Models\User user model
     * @param \Laravel\Socialite\Two\User $oauthUser oAuth user object
     */
    protected function addProfile($provider, $user, $oauthUser): void
    {
        $pageToken = $oauthUser->token;
        $token = $oauthUser->token;

        if ($provider === 'facebook') {
            // Extend current token to long-lived access token.
            /** @var \Facebook\FacebookResponse $response */
            $response = Facebook::get('/oauth/access_token?client_id=' . urlencode(env('FACEBOOK_APP_ID')) . '&client_secret=' . urlencode(env('FACEBOOK_APP_SECRET')) . '&grant_type=fb_exchange_token&fb_exchange_token=' . urlencode($oauthUser->token), $token);

            if (! isset($response->getDecodedBody()['access_token'])) {
                throw new RuntimeException('User is not a page admin of Facebook page #' . env('FACEBOOK_PAGE_ID', '') . '.');
            }

            $token = $response->getDecodedBody()['access_token'];

            // Get page token (never expires)
            try {
                $response = Facebook::get('/' . env('FACEBOOK_PAGE_ID', '') . '?fields=access_token', $token)->getGraphObject();
            } catch (FacebookResponseException $e) {
                throw new RuntimeException('User is not a page admin of Facebook page #' . env('FACEBOOK_PAGE_ID', '') . '.');
            }

            $pageToken = $response['access_token'];
        }

        $user->profiles()->save(new UserProfile([
            'provider_id' => $oauthUser->id,
            'provider_name' => $provider,
            'provider_token' => $oauthUser->token,
            'page_token' => $pageToken,
            'data' => json_encode($oauthUser->user),
        ]));
    }
}
