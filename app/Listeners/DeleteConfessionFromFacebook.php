<?php

namespace NUSWhispers\Listeners;

use Facebook\Facebook;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteConfessionFromFacebook implements ShouldQueue
{
    use ResolvesFacebookPageToken;

    /** @var \Facebook\Facebook */
    protected $fb;

    /**
     * Constructs an instance of the event listener.
     *
     * @param \Facebook\Facebook $fb
     */
    public function __construct(Facebook $fb)
    {
        $this->fb = $fb;
    }

    /**
     * Handle the event.
     *
     * @param mixed $event
     *
     * @return mixed
     *
     * @throws \Facebook\Exceptions\FacebookSDKException
     */
    public function handle($event)
    {
        $confession = $event->confession;

        if (empty($confession->fb_post_id)) {
            return true;
        }

        if (
            ! config('app.manual_mode') &&
            (! $confession->exists || in_array($confession->status, ['Pending', 'Rejected'], true))
        ) {
            $endpoint = $confession->images ?
                '/' . $confession->fb_post_id :
                '/' . config('services.facebook.page_id') . '_' . $confession->fb_post_id;

            $this->fb->delete($endpoint, [], $this->resolvePageToken($event->user));
        }

        $confession->update(['fb_post_id' => '']);
    }
}
