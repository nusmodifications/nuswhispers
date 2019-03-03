<?php

namespace NUSWhispers\Services;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use NUSWhispers\Models\Confession;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk as Facebook;

class FacebookBatchProcessor
{
    /**
     * Facebook page access token.
     *
     * @var string
     */
    protected $accessToken;

    /**
     * Facebook object.
     *
     * @var \SammyK\LaravelFacebookSdk\LaravelFacebookSdk
     */
    protected $fb;

    /**
     * Creates a new FacebookBatchProcessor instance.
     *
     * @param Facebook $fb
     */
    public function __construct(Facebook $fb)
    {
        $this->accessToken = config('laravel-facebook-sdk.facebook_config.page_access_token');
        $this->fb = $fb;

        $this->fb->setDefaultAccessToken($this->accessToken);
    }

    /**
     * Requests information from Facebook about the confession.
     *
     * @param \NUSWhispers\Models\Confession $confession
     *
     * @return \NUSWhispers\Models\Confession $confession
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function processConfession(Confession $confession): Confession
    {
        $confessions = collect([$confession]);
        $confessions = $this->processConfessions($confessions);

        return $confessions->first();
    }

    /**
     * Batch requests information from Facebook about the confessions.
     * This is faster than individually sending a request per confession.
     *
     * @param \Illuminate\Support\Collection $confessions List of confessions.
     *
     * @return \Illuminate\Support\Collection List of processed confessions.
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function processConfessions(Collection $confessions): Collection
    {
        $batchRequests = [];

        if (! $confessions->count()) {
            return $confessions;
        }

        foreach ($confessions as $confession) {
            $requestUrl = sprintf(
                '/%s?oauth_token=%s&fields=%scomments.summary(true),likes.summary(true)',
                $this->parseFacebookPostId($confession),
                $this->accessToken,
                ! empty($confession->getAttribute('images')) ? 'images,' : ''
            );

            $batchRequests[$confession->getAttribute('confession_id')] = $this->fb->request('GET', $requestUrl);
        }

        $responses = $this->fb->sendBatchRequest($batchRequests);

        $confessions->map(function (Confession $confession) use ($responses) {
            $fbResponse = $responses[$confession->getAttribute('confession_id')]->getDecodedBody();
            $confession->setAttribute('status_updated_at_timestamp', $confession->status_updated_at->timestamp);
            $confession->setAttribute('facebook_information', $fbResponse);

            // Update image field with Facebook's image URL.
            if ($facebookImage = Arr::get($fbResponse, 'images.0.source')) {
                $confession->setAttribute('images', $facebookImage);
            }
        });

        return $confessions;
    }

    /**
     * Parse the correct Facebook post ID to pass in through.
     *
     * @param \NUSWhispers\Models\Confession $confession
     *
     * @return mixed|string
     */
    protected function parseFacebookPostId(Confession $confession)
    {
        return $confession->getAttribute('images') ?
            $confession->getAttribute('fb_post_id') :
            config('services.facebook.page_id') . '_' . $confession->getAttribute('fb_post_id');
    }
}
