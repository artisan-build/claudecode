<?php

namespace ArtisanBuild\ClaudeCode\Contracts;

use ArtisanBuild\ClaudeCode\Support\ClaudeCodeOptions;
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeQuery;

interface ClaudeCodeClient
{
    public function query(string $prompt): ClaudeCodeQuery;

    public function execute(ClaudeCodeQuery $query): array;

    public function stream(ClaudeCodeQuery $query, callable $callback): void;

    public function setDefaultOptions(ClaudeCodeOptions $options): self;

    public function setTimeout(int $seconds): self;
}