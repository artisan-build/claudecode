<?php

namespace ArtisanBuild\ClaudeCode\Exceptions;

class ProcessException extends ClaudeCodeException
{
    protected int $exitCode = 0;

    public function __construct(string $message = '', int $exitCode = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $exitCode, $previous);
        $this->exitCode = $exitCode;
    }

    public function getExitCode(): int
    {
        return $this->exitCode;
    }
}
