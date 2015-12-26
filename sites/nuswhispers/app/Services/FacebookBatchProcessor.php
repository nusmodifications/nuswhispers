<?php

namespace App\Services;

use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk as Facebook;
use Config;

class FacebookBatchProcessor
{
    /**
     * Facebook page access token.
     * @var string
     */
    protected $accessToken;

    /**
     * Facebook object.
     * @var SammyK\LaravelFacebookSdk\LaravelFacebookSdk
     */
    protected $fb;

    /**
     * Creates a new FacebookBatchProcessor instance.
     *
     * @param Facebook $fb
     */
    public function __construct(Facebook $fb)
    {
        $this->accessToken = Config::get('laravel-facebook-sdk.facebook_config.page_access_token');
        $this->fb = $fb;

        $this->fb->setDefaultAccessToken($this->accessToken);
    }

    /**
     * Requests information from Facebook about the confession.
     *
     * @param  \App\Models\Confession $confession
     * @return \App\Models\Confession $confession
     */
    public function processConfession($confession)
    {
        $confessions = collect([$confession]);
        $confessions = $this->processConfessions($confessions);

        return $confessions->first();
    }

    /**
     * Batch requests information from Facebook about the confessions.
     * This is faster than individually sending a request per confession.
     *
     * @param  \Illuminate\Database\Eloquent\Collection $confessions  List of confessions.
     * @return \Illuminate\Database\Eloquent\Collection List of processed confessions.
     */
    public function processConfessions($confessions)
    {
        $batchRequests = [];

        if (!$confessions->count()) {
            return $confessions;
        }

        foreach ($confessions as $confession) {
            $requestUrl = sprintf('/%s?oauth_token=%s&fields=comments.summary(true).filter(toplevel).fields(parent.fields(id),comments.summary(true),message,from,created_time),likes.summary(true)', $confession->getAttribute('fb_post_id'), $this->accessToken);

            $batchRequests[$confession->getAttribute('confession_id')] = $this->fb->request('GET', $requestUrl);
        }

        $responses = $this->fb->sendBatchRequest($batchRequests);

        $confessions->map(function ($confession) use ($responses) {
            $facebookResponse = $responses[$confession->getAttribute('confession_id')]->getDecodedBody();
            $confession->setAttribute('status_updated_at_timestamp', $confession->status_updated_at->timestamp);
            $confession->setAttribute('facebook_information', $facebookResponse);
        });

        return $confessions;
    }
}
