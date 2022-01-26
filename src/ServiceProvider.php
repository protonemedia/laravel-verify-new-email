<?php

namespace ProtoneMedia\LaravelVerifyNewEmail;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(Filesystem $filesystem)
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'verify-new-email');

        if (!config('verify-new-email.route')) {
            $this->loadRoutesFrom(__DIR__ . '/routes.php');
        }

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../database/migrations/create_pending_user_emails_table.php.stub'      => $this->getMigrationFileName($filesystem, 'create_pending_user_emails_table', 0),
                __DIR__ . '/../database/migrations/add_type_to_pending_user_emails_table.php.stub' => $this->getMigrationFileName($filesystem, 'add_type_to_pending_user_emails_table', 1),
            ], 'migrations');

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
     * Returns existing migration file if found, else uses the current timestamp.
     *
     * @param Filesystem $filesystem
     * @return string
     */
    protected function getMigrationFileName(Filesystem $filesystem, $name, $addSeconds): string
    {
        $timestamp = now()->addSeconds($addSeconds)->format('Y_m_d_His');

        return Collection::make($this->app->databasePath('migrations') . DIRECTORY_SEPARATOR)
            ->flatMap(function ($path) use ($filesystem, $name) {
                return $filesystem->glob("{$path}*_{$name}.php");
            })
            ->push($this->app->databasePath("migrations/{$timestamp}_{$name}.php"))
            ->first();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'verify-new-email');
    }
}
