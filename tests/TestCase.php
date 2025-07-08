<?php

namespace ArtisanBuild\ClaudeCode\Tests;

use ArtisanBuild\ClaudeCode\Providers\ClaudeCodeServiceProvider;
use Illuminate\Database\Eloquent\Factories\Factory;
use Orchestra\Testbench\TestCase as Orchestra;
use Override;

/**
 * Remove the .forked from the end of this filename to enable stand-alone testing on this package when forked.
 */
class TestCase extends Orchestra
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        Factory::guessFactoryNamesUsing(
            fn (string $modelName) => 'ArtisanBuild\\ClaudeCode\\Database\\Factories\\'.class_basename($modelName).'Factory'
        );
    }

    protected function getPackageProviders($app)
    {
        return [
            ClaudeCodeServiceProvider::class,
        ];
    }

    public function getEnvironmentSetUp($app)
    {
        config()->set('database.default', 'testing');

        /*
         foreach (\Illuminate\Support\Facades\File::allFiles(__DIR__ . '/database/migrations') as $migration) {
            (include $migration->getRealPath())->up();
         }
         */
    }
}
