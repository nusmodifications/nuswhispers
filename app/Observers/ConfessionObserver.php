<?php

namespace App\Observers;

use App\Jobs\LogConfession;
use App\Models\Confession;

class ConfessionObserver
{
    /**
     * Listen to the 'saved' event.
     *
     * @param  App\Models\Confession $confession
     * @return void
     */
    public function saved(Confession $confession)
    {
        $user = $this->resolveUser($confession);

        if (! $user) {
            return true;
        }

        $job = new LogConfession(
            $user,
            $confession->getOriginal('status'),
            $confession
        );

        dispatch($job);
    }

    /**
     * Resolves the user who changed the status of the confession.
     *
     * @param  App\Models\Confession $confession
     * @return App\Models\User|null
     */
    protected function resolveUser($confession)
    {
        if (auth()->check()) {
            return auth()->user();
        }

        $lastLog = $confession
            ->logs()
            ->orderBy('created_on', 'desc')
            ->with(['user', 'user.profiles'])
            ->first();

        if (! $lastLog) {
            return;
        }

        return $lastLog->user;
    }
}
