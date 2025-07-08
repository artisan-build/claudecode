<?php

namespace ArtisanBuild\ClaudeCode\Tests\Mocks;

use ArtisanBuild\ClaudeCode\ClaudeCode;
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeOptions;

class MockClaudeCode extends ClaudeCode
{
    public function __construct(
        ?string $cliPath = null,
        ?ClaudeCodeOptions $defaultOptions = null,
        ?int $timeout = null
    ) {
        $this->cliPath = $cliPath ?? 'claude';
        $this->timeout = $timeout ?? 120;
        $this->defaultOptions = $defaultOptions;

        // Skip CLI validation in tests
    }
}
