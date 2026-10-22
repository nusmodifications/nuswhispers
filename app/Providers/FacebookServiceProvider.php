<?php

namespace NUSWhispers\Providers;

use Facebook\Facebook;
use GuzzleHttp\Client;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class FacebookServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides(): array
    {
        return ['facebook', Facebook::class];
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
                // SDK 6 drops request headers while building its PSR-7 request.
                'http_client' => $app->make(Client::class, [
                    'config' => ['headers' => ['Content-Type' => 'application/x-www-form-urlencoded']],
                ]),
            ]);
        });

        $this->app->alias('facebook', Facebook::class);
    }
}
