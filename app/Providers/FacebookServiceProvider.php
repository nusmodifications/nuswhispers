<?php

namespace NUSWhispers\Providers;

use Facebook\Facebook;
use Illuminate\Contracts\Container\Container;
use Illuminate\Support\ServiceProvider;

class FacebookServiceProvider extends ServiceProvider
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
    public function provides(): array
    {
        return [Facebook::class];
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton('facebook', static function (Container $app) {
            /** @var \Illuminate\Contracts\Config\Repository $config */
            $config = $app['config'];

            return new Facebook([
                'app_id' => $config->get('services.facebook.client_id'),
                'app_secret' => $config->get('services.facebook.client_secret'),
                'default_graph_version' => $config->get('services.facebook.default_graph_version'),
                'default_access_token' => $config->get('services.facebook.page_access_token'),
            ]);
        });

        $this->app->alias('facebook', Facebook::class);
    }
}
