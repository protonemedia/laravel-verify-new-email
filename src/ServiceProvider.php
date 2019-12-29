<?php

namespace Protonemedia\LaravelVerifyNewEmail;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'verify-new-email');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('verify-new-email.php'),
            ], 'config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/verify-new-email'),
            ], 'views');

            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'verify-new-email');
    }
}
