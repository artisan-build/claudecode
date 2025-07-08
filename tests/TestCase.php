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

        // Use mock implementation in tests to avoid CLI validation
        $this->app->bind(ClaudeCodeClient::class, MockClaudeCode::class);
        $this->app->bind(ClaudeCode::class, MockClaudeCode::class);
        $this->app->bind('claude-code', MockClaudeCode::class);
    }
}
