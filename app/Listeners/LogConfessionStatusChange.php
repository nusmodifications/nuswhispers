<?php

namespace NUSWhispers\Listeners;

use NUSWhispers\Events\ConfessionStatusWasChanged;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogConfessionStatusChange implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  \NUSWhispers\Events\ConfessionStatusWasChanged $event
     * @return mixed
     */
    public function handle(ConfessionStatusWasChanged $event)
    {
        $confession = $event->confession;
        $originalStatus = $event->originalStatus;

        if ($confession->status === $originalStatus) {
            return true;
        }

        $confession->logs()->create([
            'confession_id' => $confession->getKey(),
            'status_before' => $originalStatus,
            'status_after' => $confession->status,
            'changed_by_user' => $event->user->getKey(),
            'created_on' => $confession->status_updated_at,
        ]);
    }
}
