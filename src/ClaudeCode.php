<?php

namespace ArtisanBuild\ClaudeCode;

use ArtisanBuild\ClaudeCode\Contracts\ClaudeCodeClient;
use ArtisanBuild\ClaudeCode\Exceptions\CLINotFoundException;
use ArtisanBuild\ClaudeCode\Exceptions\ClaudeCodeException;
use ArtisanBuild\ClaudeCode\Exceptions\ProcessException;
use ArtisanBuild\ClaudeCode\Messages\Message;
use ArtisanBuild\ClaudeCode\Messages\MessageFactory;
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeOptions;
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeQuery;
use Illuminate\Support\Facades\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process as SymfonyProcess;

class ClaudeCode implements ClaudeCodeClient
{
    protected string $cliPath = 'claude';

    protected ?ClaudeCodeOptions $defaultOptions = null;

    protected int $timeout = 120;

    public function __construct(
        ?string $cliPath = null,
        ?ClaudeCodeOptions $defaultOptions = null,
        ?int $timeout = null
    ) {
        $this->cliPath = $cliPath ?? config('claude-code.cli_path', 'claude');
        $this->defaultOptions = $defaultOptions;
        $this->timeout = $timeout ?? config('claude-code.timeout', 120);

        $this->validateCLI();
    }

    public function query(string $prompt): ClaudeCodeQuery
    {
        return new ClaudeCodeQuery($this, $prompt, $this->defaultOptions);
    }

    public function execute(ClaudeCodeQuery $query): array
    {
        $command = $this->buildCommand($query);
        $messages = [];

        try {
            $process = Process::command($command)
                ->path($query->getOptions()->workingDirectory ?? getcwd())
                ->timeout($this->timeout);

            if ($apiKey = config('claude-code.api_key')) {
                $process->env(['ANTHROPIC_API_KEY' => $apiKey]);
            }

            $result = $process->run(function (string $type, string $output) use (&$messages) {
                if ($type === SymfonyProcess::ERR) {
                    throw new ProcessException("Claude Code error: $output");
                }

                $lines = array_filter(explode("\n", trim($output)));
                foreach ($lines as $line) {
                    if ($message = $this->parseMessage($line)) {
                        $messages[] = $message;
                    }
                }
            });

            if (! $result->successful()) {
                throw new ProcessException(
                    "Claude Code process failed with exit code: {$result->exitCode()}",
                    $result->exitCode()
                );
            }

            return $messages;
        } catch (ProcessFailedException $e) {
            throw new ProcessException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function stream(ClaudeCodeQuery $query, callable $callback): void
    {
        $command = $this->buildCommand($query);

        try {
            $process = Process::command($command)
                ->path($query->getOptions()->workingDirectory ?? getcwd())
                ->timeout($this->timeout);

            if ($apiKey = config('claude-code.api_key')) {
                $process->env(['ANTHROPIC_API_KEY' => $apiKey]);
            }

            $process->run(function (string $type, string $output) use ($callback) {
                if ($type === SymfonyProcess::ERR) {
                    throw new ProcessException("Claude Code error: $output");
                }

                $lines = array_filter(explode("\n", trim($output)));
                foreach ($lines as $line) {
                    if ($message = $this->parseMessage($line)) {
                        $callback($message);
                    }
                }
            });

            if (! $process->successful()) {
                throw new ProcessException(
                    "Claude Code process failed with exit code: {$process->exitCode()}",
                    $process->exitCode()
                );
            }
        } catch (ProcessFailedException $e) {
            throw new ProcessException($e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function buildCommand(ClaudeCodeQuery $query): array
    {
        $command = [$this->cliPath, 'code'];
        $options = $query->getOptions();

        if ($options->systemPrompt) {
            $command[] = '--system-prompt';
            $command[] = $options->systemPrompt;
        }

        if ($options->maxTurns) {
            $command[] = '--max-turns';
            $command[] = (string) $options->maxTurns;
        }

        if ($options->model) {
            $command[] = '--model';
            $command[] = $options->model;
        }

        if ($options->permissionMode) {
            $command[] = '--permission-mode';
            $command[] = $options->permissionMode;
        }

        if ($options->allowedTools) {
            foreach ($options->allowedTools as $tool) {
                $command[] = '--allowed-tool';
                $command[] = $tool;
            }
        }

        $command[] = '--json';
        $command[] = '--no-color';
        $command[] = $query->getPrompt();

        return $command;
    }

    protected function parseMessage(string $line): ?Message
    {
        $line = trim($line);
        if (empty($line)) {
            return null;
        }

        try {
            $data = json_decode($line, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                return null;
            }

            return MessageFactory::create($data);
        } catch (\Exception $e) {
            return null;
        }
    }

    protected function validateCLI(): void
    {
        try {
            $result = Process::command([$this->cliPath, '--version'])
                ->timeout(5)
                ->run();

            if (! $result->successful()) {
                throw new CLINotFoundException(
                    "Claude CLI not found or not executable at path: {$this->cliPath}"
                );
            }
        } catch (\Exception $e) {
            throw new CLINotFoundException(
                "Claude CLI not found at path: {$this->cliPath}. Please ensure Claude Code is installed.",
                0,
                $e
            );
        }
    }

    public function setDefaultOptions(ClaudeCodeOptions $options): self
    {
        $this->defaultOptions = $options;

        return $this;
    }

    public function setTimeout(int $seconds): self
    {
        $this->timeout = $seconds;

        return $this;
    }
}