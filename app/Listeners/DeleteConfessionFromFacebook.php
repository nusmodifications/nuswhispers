<?php

namespace NUSWhispers\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use SammyK\LaravelFacebookSdk\LaravelFacebookSdk;

class DeleteConfessionFromFacebook implements ShouldQueue
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
     * @param  mixed $event
     *
     * @return mixed
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
