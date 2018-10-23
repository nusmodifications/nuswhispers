<?php

namespace NUSWhispers\Listeners;

use NUSWhispers\Models\User;

trait ResolvesFacebookPageToken
{
    /**
     * Resolves the Facebook Page token to be used to post / delete the confession.
     *
     * @param mixed $user
     *
     * @return string
     */
    public function resolvePageToken($user): string
    {
        if (! $user instanceof User) {
            return config('laravel-facebook-sdk.facebook_config.page_access_token');
        }

        $profile = $user->profiles()->where('provider_name', 'facebook')->first();

        if ($profile && ! empty($profile->page_token)) {
            return $profile->page_token;
        }

        return config('laravel-facebook-sdk.facebook_config.page_access_token');
    }
}
