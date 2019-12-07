<?php

namespace NUSWhispers\Console\Commands;

use Facebook\Facebook;
use Illuminate\Console\Command;
use NUSWhispers\Models\Confession;

class UpdateConfessionFacebookInfo extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Updates the latest 250 confessions with Facebook information.';

    /**
     * @var \Facebook\Facebook
     */
    protected $fb;

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'confession:facebook-update';

    /**
     * Constructs an instance of the command.
     *
     * @param \Facebook\Facebook $fb
     */
    public function __construct(Facebook $fb)
    {
        parent::__construct();

        $this->fb = $fb;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function handle()
    {
        $accessToken = config('laravel-facebook-sdk.facebook_config.page_access_token');

        // Get the latest 250 facebook posts and record likes/comments
        $facebookRequest = sprintf('/%s/feed?limit=100&oauth_token=%s&fields=comments.summary(true),likes.summary(true)', config('services.facebook.page_id'), $accessToken);
        $facebookResponse = $this->fb->get($facebookRequest, $accessToken)->getDecodedBody();
        foreach ($facebookResponse['data'] as $facebookPost) {
            $facebookPostId = explode('_', $facebookPost['id'])[1]; // get facebook post id
            $confession = Confession::query()->where('fb_post_id', '=', $facebookPostId)->first(); // get confession associated with fb post
            if ($confession) {
                $confession->fb_like_count = $facebookPost['likes']['summary']['total_count'];
                $confession->fb_comment_count = $facebookPost['comments']['summary']['total_count'];
                $confession->save();
            }
        }

        $this->comment('Facebook Information Updated!');
    }
}
