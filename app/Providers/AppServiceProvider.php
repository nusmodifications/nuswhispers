<?php

namespace NUSWhispers\Providers;

use NUSWhispers\Models\Confession;
use NUSWhispers\Observers\ConfessionObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Confession::observe(ConfessionObserver::class);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
