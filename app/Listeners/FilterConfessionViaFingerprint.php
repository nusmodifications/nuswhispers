<?php

namespace NUSWhispers\Listeners;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use NUSWhispers\Events\ConfessionWasCreated;
use anlutro\LaravelSettings\Facade as Settings;
use NUSWhispers\Models\Confession;

class FilterConfessionViaFingerprint implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param  ConfessionWasCreated  $event
     * @return mixed
     */
    public function handle(ConfessionWasCreated $event)
    {
        $threshold = (int) Settings::get('rejection_net_score', 5);
        $decay = (int) Settings::get('rejection_decay', 30);

        if (! $threshold || ! $decay) {
            return true;
        }

        $confession = $event->confession;
        $score = $this->getNetScore($confession->fingerprint, $decay);

        if ($score > $threshold) {
            $confession->update(['status' => 'Rejected']);
        }
    }

    /**
     * Get net score based on fingerprint.
     *
     * @param string $fingerprint
     * @param int $decay
     *
     * @return int
     */
    protected function getNetScore($fingerprint, $decay)
    {
        $rejectedCount = Confession::rejected()
            ->where('fingerprint', $fingerprint)
            ->where('created_at', '>', Carbon::now()->subDays($decay))
            ->count();

        $approvedCount = Confession::approved()
            ->where('fingerprint', $fingerprint)
            ->where('created_at', '>', Carbon::now()->subDays($decay))
            ->count();

        return max(0, $rejectedCount - $approvedCount);
    }
}
