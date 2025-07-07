<?php

namespace ArtisanBuild\ClaudeCode\Support;

use ArtisanBuild\ClaudeCode\ClaudeCode;
use ArtisanBuild\ClaudeCode\Messages\Message;

class ClaudeCodeQuery
{
    protected ClaudeCodeOptions $options;

    public function __construct(protected ClaudeCode $client, protected string $prompt, ?ClaudeCodeOptions $defaultOptions = null)
    {
        $this->options = $defaultOptions ? clone $defaultOptions : new ClaudeCodeOptions;
    }

    public function withSystemPrompt(string $systemPrompt): self
    {
        $this->options->systemPrompt = $systemPrompt;

        return $this;
    }

    public function withMaxTurns(int $maxTurns): self
    {
        $this->options->maxTurns = $maxTurns;

        return $this;
    }

    public function withModel(string $model): self
    {
        $this->options->model = $model;

        return $this;
    }

    public function withWorkingDirectory(string $directory): self
    {
        $this->options->workingDirectory = $directory;

        return $this;
    }

    public function withPermissionMode(string $mode): self
    {
        $this->options->permissionMode = $mode;

        return $this;
    }

    public function allowTools(array $tools): self
    {
        $this->options->allowedTools = $tools;

        return $this;
    }

    public function withOptions(ClaudeCodeOptions $options): self
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Execute the query and return all messages
     *
     * @return array<Message>
     */
    public function execute(): array
    {
        return $this->client->execute($this);
    }

    /**
     * Execute the query and stream messages as they arrive
     */
    public function stream(callable $callback): void
    {
        $this->client->stream($this, $callback);
    }

    /**
     * Execute the query and return only the final result
     */
    public function get(): ?string
    {
        $messages = $this->execute();
        $content = [];

        foreach ($messages as $message) {
            if ($message->type === 'assistant' && isset($message->content)) {
                foreach ($message->content as $block) {
                    if (isset($block['type']) && $block['type'] === 'text' && isset($block['text'])) {
                        $content[] = $block['text'];
                    }
                }
            }
        }

        return implode("\n", $content) ?: null;
    }

    public function getPrompt(): string
    {
        return $this->prompt;
    }

    public function getOptions(): ClaudeCodeOptions
    {
        return $this->options;
    }
}
