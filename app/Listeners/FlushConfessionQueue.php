<?php

namespace NUSWhispers\Listeners;

use NUSWhispers\Events\ConfessionStatusWasChanged;
use Illuminate\Contracts\Queue\ShouldQueue;

class FlushConfessionQueue implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  ConfessionStatusWasChanged  $event
     * @return void
     */
    public function handle(ConfessionStatusWasChanged $event)
    {
        $confession = $event->confession;

        if ($confession->status !== 'Scheduled') {
            $confession->queue()->delete();
        }
    }
}
