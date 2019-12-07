<?php

namespace NUSWhispers\Listeners;

use anlutro\LaravelSettings\Facade as Settings;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use NUSWhispers\Events\ConfessionWasCreated;

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
        $blacklist = strtolower(Settings::get('word_blacklist', ''));
        $confession = $event->confession;

        if (Str::contains(strtolower($confession->content), explode(',', $blacklist))) {
            $confession->update(['status' => 'Rejected']);
        }
    }
}
