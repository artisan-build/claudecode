<?php

namespace ArtisanBuild\ClaudeCode\Support;

class ClaudeCodeOptions
{
    public ?string $systemPrompt = null;

    public ?int $maxTurns = null;

    public ?array $allowedTools = null;

    public ?string $permissionMode = null;

    public ?string $workingDirectory = null;

    public ?string $model = null;

    public static function create(): self
    {
        return new self;
    }

    public function systemPrompt(string $prompt): self
    {
        $this->systemPrompt = $prompt;

        return $this;
    }

    public function maxTurns(int $turns): self
    {
        $this->maxTurns = $turns;

        return $this;
    }

    public function allowedTools(array $tools): self
    {
        $this->allowedTools = $tools;

        return $this;
    }

    public function permissionMode(string $mode): self
    {
        $this->permissionMode = $mode;

        return $this;
    }

    public function workingDirectory(string $directory): self
    {
        $this->workingDirectory = $directory;

        return $this;
    }

    public function model(string $model): self
    {
        $this->model = $model;

        return $this;
    }

    public function toArray(): array
    {
        return array_filter([
            'system_prompt' => $this->systemPrompt,
            'max_turns' => $this->maxTurns,
            'allowed_tools' => $this->allowedTools,
            'permission_mode' => $this->permissionMode,
            'working_directory' => $this->workingDirectory,
            'model' => $this->model,
        ], fn ($value) => $value !== null);
    }
}