<?php

namespace NUSWhispers\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'NUSWhispers\Events\ConfessionWasCreated' => [
            'NUSWhispers\Listeners\SyncConfessionTags',
        ],
        'NUSWhispers\Events\ConfessionWasUpdated' => [
            'NUSWhispers\Listeners\SyncConfessionTags',
        ],
        'NUSWhispers\Events\ConfessionWasDeleted' => [
            'NUSWhispers\Listeners\DeleteConfessionFromFacebook',
        ],
        'NUSWhispers\Events\ConfessionStatusWasChanged' => [
            'NUSWhispers\Listeners\FlushConfessionQueue',
            'NUSWhispers\Listeners\LogConfessionStatusChange',
        ],
        'NUSWhispers\Events\ConfessionWasApproved' => [
            'NUSWhispers\Listeners\PostConfessionToFacebook',
        ],
        'NUSWhispers\Events\ConfessionWasFeatured' => [
            'NUSWhispers\Listeners\PostConfessionToFacebook',
        ],
        'NUSWhispers\Events\ConfessionWasRejected' => [
            'NUSWhispers\Listeners\DeleteConfessionFromFacebook',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
