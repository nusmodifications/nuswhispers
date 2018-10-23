<?php

namespace NUSWhispers\Providers;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;
use ReCaptcha\ReCaptcha;

class ReCaptchaServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [ReCaptcha::class];
    }

    /**
     * Register the service provider.
     *
     * @return void
     *
     * @throws \RuntimeException
     */
    public function register()
    {
        $this->app->singleton('recaptcha', function (Container $app) {
            return new ReCaptcha($app['config']->get('services.recaptcha.key'));
        });

        $this->app->alias('recaptcha', ReCaptcha::class);
    }
}
