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

    protected function user($email = 'old@example.com')
    {
        return User::create([
            'name'     => 'Demo User',
            'email'    => $email,
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
        include_once __DIR__ . '/../database/migrations/create_pending_user_emails_table.php.stub';
        include_once __DIR__ . '/../database/migrations/add_type_to_pending_user_emails_table.php.stub';

        (new \CreateUsersTable)->up();
        (new \CreatePendingUserEmailsTable)->up();
        (new \AddTypeToPendingUserEmailsTable)->up();

        $this->app['config']->set('auth.providers.users.model', User::class);
    }
}
