<?php

namespace NUSWhispers\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use NUSWhispers\Events\ConfessionStatusWasChanged;

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
