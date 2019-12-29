<?php

namespace ProtoneMedia\LaravelVerifyNewEmail;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'verify-new-email');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if (!class_exists('CreatePendingUserEmailsTable')) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_pending_user_emails_table.php.stub' => database_path('migrations/' . date('Y_m_d_His', time()) . '_create_pending_user_emails_table.php'),
            ], 'migrations');
        }

        if (!config('verify-new-email.route')) {
            $this->loadRoutesFrom(__DIR__ . '/routes.php');
        }

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
