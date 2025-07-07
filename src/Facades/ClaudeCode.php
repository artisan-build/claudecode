<?php

namespace ArtisanBuild\ClaudeCode\Facades;

use ArtisanBuild\ClaudeCode\Contracts\ClaudeCodeClient;
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeOptions;
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeQuery;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ClaudeCodeQuery query(string $prompt)
 * @method static array execute(ClaudeCodeQuery $query)
 * @method static void stream(ClaudeCodeQuery $query, callable $callback)
 * @method static self setDefaultOptions(ClaudeCodeOptions $options)
 * @method static self setTimeout(int $seconds)
 *
 * @see \ArtisanBuild\ClaudeCode\ClaudeCode
 */
class ClaudeCode extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return ClaudeCodeClient::class;
    }
}
