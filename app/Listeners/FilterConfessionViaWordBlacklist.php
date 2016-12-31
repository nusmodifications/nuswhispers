<?php

namespace NUSWhispers\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use NUSWhispers\Events\ConfessionWasCreated;
use anlutro\LaravelSettings\Facade as Settings;

class FilterConfessionViaWordBlacklist implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  ConfessionWasCreated  $event
     * @return void
     */
    public function handle(ConfessionWasCreated $event)
    {
        $blacklist = Settings::get('word_blacklist', '');
        $confession = $event->confession;

        if (str_contains($confession->content, explode(',', $blacklist))) {
            $confession->update(['status' => 'Rejected']);
        }
    }
}
