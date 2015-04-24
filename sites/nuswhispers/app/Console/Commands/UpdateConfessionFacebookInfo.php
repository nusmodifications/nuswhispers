<?php namespace App\Console\Commands;

use App\Models\Confession as Confession;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpdateConfessionFacebookInfo extends Command {

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'confession:facebook-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        // Get the latest 250 facebook posts and record likes/comments
        $facebookRequest = sprintf('/%s/feed?limit=250&oauth_token=%s&fields=comments.limit(1).summary(true),likes.limit(1).summary(true)', env('FACEBOOK_PAGE_ID', ''), \Config::get('laravel-facebook-sdk.facebook_config.page_access_token'));
        $facebookResponse = \Facebook::get($facebookRequest)->getDecodedBody();
        foreach ($facebookResponse['data'] as $facebookPost) {
            $facebookPostId = explode('_', $facebookPost['id'])[1]; // get facebook post id
            $confession = Confession::where('fb_post_id', '=', $facebookPostId)->first(); // get confession associated with fb post
            if ($confession) {
                $confession->fb_like_count = $facebookPost['likes']['summary']['total_count'];
                $confession->fb_comment_count = $facebookPost['comments']['summary']['total_count'];
                $confession->save();
            }
        }
        $this->info('Facebook Information Updated!');
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            // ['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            // ['example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null],
        ];
    }

}
