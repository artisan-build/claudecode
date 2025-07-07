<?php

namespace ArtisanBuild\ClaudeCode\Tests;

use ArtisanBuild\ClaudeCode\Providers\ClaudeCodeServiceProvider;
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
    }
}
