<?php

namespace NUSWhispers\Listeners;

use NUSWhispers\Models\Confession;
use Illuminate\Contracts\Queue\ShouldQueue;
use NUSWhispers\Events\BaseConfessionEvent;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;

class PostConfessionToFacebook implements ShouldQueue
{
    use ResolvesFacebookPageToken;

    /** @var \SammyK\LaravelFacebookSdk\LaravelFacebookSdk */
    protected $fb;

    /**
     * Constructs an instance of the event listener.
     *
     * @param \SammyK\LaravelFacebookSdk\LaravelFacebookSdk $fb
     */
    public function __construct(LaravelFacebookSdk $fb)
    {
        $this->fb = $fb;
    }

    /**
     * Handle the event.
     *
     * @param  \NUSWhispers\Events\BaseConfessionEvent $event
     *
     * @return mixed
     */
    public function handle(BaseConfessionEvent $event)
    {
        if (config('app.manual_mode')) {
            return true;
        }

        $confession = $event->confession;

        // Someone might have changed his/her mind...
        if (! in_array($confession->status, ['Approved', 'Featured'])) {
            return true;
        }

        $confession->fb_post_id = ! empty($confession->images) ?
            $this->createOrUpdatePhoto($confession, $event->user) :
            $this->createOrUpdateStatus($confession, $event->user);

        $confession->save();
    }

    /**
     * Create or update a Facebook photo post.
     *
     * @param \NUSWhispers\Models\Confession $confession
     * @param mixed $user
     *
     * @return string
     */
    protected function createOrUpdatePhoto(Confession $confession, $user)
    {
        $fbPostId = $confession->getAttribute('fb_post_id');

        $endpoint = $fbPostId ?
            '/' . $fbPostId :
            '/' . config('services.facebook.page_id') . '/photos';

        $response = $this->fb->post(
            $endpoint,
            [
                'message' => $confession->getFacebookMessage(),
                'url' => $confession->images,
            ],
            $this->resolvePageToken($user)
        )->getGraphNode();

        return $fbPostId ?: $response['id'];
    }

    /**
     * Create or update a Facebook status update.
     *
     * @param \NUSWhispers\Models\Confession $confession
     * @param mixed $user
     *
     * @return string
     */
    protected function createOrUpdateStatus(Confession $confession, $user)
    {
        $fbPostId = $confession->getAttribute('fb_post_id');

        $endpoint = $fbPostId ?
            '/' . config('services.facebook.page_id') . '_' . $fbPostId :
            '/' . config('services.facebook.page_id') . '/feed';

        $response = $this->fb->post(
            $endpoint,
            [
                'message' => $confession->getFacebookMessage(),
            ],
            $this->resolvePageToken($user)
        )->getGraphNode();

        return $fbPostId ?: last(explode('_', $response['id']));
    }
}
