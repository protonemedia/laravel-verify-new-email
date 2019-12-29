<?php

namespace ProtoneMedia\LaravelVerifyNewEmail\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use ProtoneMedia\LaravelVerifyNewEmail\ServiceProvider;

class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function user()
    {
        return User::create([
            'name'     => 'Demo User',
            'email'    => 'old@example.com',
            'password' => 'secret',
        ]);
    }

    public function setUp():void
    {
        parent::setUp();

        $this->app['config']->set('app.key', 'base64:yWa/ByhLC/GUvfToOuaPD7zDwB64qkc/QkaQOrT5IpE=');
        $this->app['config']->set('mail.driver', 'log');
        $this->app['config']->set('database.default', 'sqlite');
        $this->app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        include_once __DIR__ . '/create_users_table.php.stub';
        include_once __DIR__ . '/../database/migrations/create_pending_email_addresses_table.php.stub';

        (new \CreateUsersTable)->up();
        (new \CreatePendingEmailAddressesTable)->up();

        User::unguard();
        $this->app['config']->set('auth.providers.users.model', User::class);
    }
}
