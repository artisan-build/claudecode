<?php

namespace ArtisanBuild\ClaudeCode\Tests;

use ArtisanBuild\ClaudeCode\ClaudeCode;
use ArtisanBuild\ClaudeCode\Contracts\ClaudeCodeClient;
use ArtisanBuild\ClaudeCode\Providers\ClaudeCodeServiceProvider;
use ArtisanBuild\ClaudeCode\Tests\Mocks\MockClaudeCode;
use Orchestra\Testbench\TestCase as Orchestra;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            ClaudeCodeServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        // Override the service provider's binding to use mock implementation
        $this->afterApplicationCreated(function (): void {
            $this->app->singleton(ClaudeCodeClient::class, MockClaudeCode::class);
            $this->app->singleton(ClaudeCode::class, MockClaudeCode::class);
            $this->app->singleton('claude-code', MockClaudeCode::class);
        });
    }
}
