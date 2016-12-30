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
        \NUSWhispers\Events\ConfessionWasCreated::class => [
            \NUSWhispers\Listeners\SyncConfessionTags::class,
        ],
        \NUSWhispers\Events\ConfessionWasUpdated::class => [
            \NUSWhispers\Listeners\SyncConfessionTags::class,
        ],
        \NUSWhispers\Events\ConfessionWasDeleted::class => [
        ],
        \NUSWhispers\Events\ConfessionStatusWasChanged::class => [
            \NUSWhispers\Listeners\FlushConfessionQueue::class,
            \NUSWhispers\Listeners\LogConfessionStatusChange::class,
        ],
        \NUSWhispers\Events\ConfessionWasApproved::class => [
        ],
        \NUSWhispers\Events\ConfessionWasFeatured::class => [
        ],
        \NUSWhispers\Events\ConfessionWasRejected::class => [
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
