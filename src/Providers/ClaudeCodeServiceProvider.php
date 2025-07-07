<?php

namespace ArtisanBuild\ClaudeCode\Providers;

use ArtisanBuild\ClaudeCode\ClaudeCode;
use ArtisanBuild\ClaudeCode\Contracts\ClaudeCodeClient;
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeOptions;
use Illuminate\Support\ServiceProvider;

class ClaudeCodeServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/claudecode.php', 'claude-code');

        $this->app->singleton(ClaudeCodeClient::class, function ($app) {
            $config = $app['config']['claude-code'];

            $defaultOptions = null;
            if (! empty($config['default_options'])) {
                $defaultOptions = new ClaudeCodeOptions;
                foreach ($config['default_options'] as $key => $value) {
                    if (property_exists($defaultOptions, $key) && $value !== null) {
                        $defaultOptions->$key = $value;
                    }
                }
            }

            return new ClaudeCode(
                $config['cli_path'] ?? null,
                $defaultOptions,
                $config['timeout'] ?? null
            );
        });

        $this->app->alias(ClaudeCodeClient::class, 'claude-code');
        $this->app->alias(ClaudeCodeClient::class, ClaudeCode::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/claudecode.php' => config_path('claude-code.php'),
            ], 'claude-code-config');
        }
    }
}
