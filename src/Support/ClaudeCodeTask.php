<?php

namespace ArtisanBuild\ClaudeCode\Support;

use ArtisanBuild\ClaudeCode\Facades\ClaudeCode;
use ArtisanBuild\ClaudeCode\Messages\Message;
use Illuminate\Support\Collection;

class ClaudeCodeTask
{
    protected string $prompt;

    protected ?ClaudeCodeOptions $options = null;

    protected array $messages = [];

    protected bool $success = false;

    protected ?string $error = null;

    public function __construct(string $prompt)
    {
        $this->prompt = $prompt;
    }

    public static function create(string $prompt): self
    {
        return new self($prompt);
    }

    public function withOptions(ClaudeCodeOptions $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function withModel(string $model): self
    {
        if (! $this->options) {
            $this->options = new ClaudeCodeOptions;
        }
        $this->options->model = $model;

        return $this;
    }

    public function inDirectory(string $directory): self
    {
        if (! $this->options) {
            $this->options = new ClaudeCodeOptions;
        }
        $this->options->workingDirectory = $directory;

        return $this;
    }

    public function allowTools(array $tools): self
    {
        if (! $this->options) {
            $this->options = new ClaudeCodeOptions;
        }
        $this->options->allowedTools = $tools;

        return $this;
    }

    /**
     * Execute the task and return the result
     */
    public function run(): self
    {
        try {
            $query = ClaudeCode::query($this->prompt);

            if ($this->options) {
                $query->withOptions($this->options);
            }

            $this->messages = $query->execute();
            $this->analyzeResult();
        } catch (\Exception $e) {
            $this->success = false;
            $this->error = $e->getMessage();
        }

        return $this;
    }

    /**
     * Execute the task and stream the results
     */
    public function stream(callable $callback): self
    {
        try {
            $query = ClaudeCode::query($this->prompt);

            if ($this->options) {
                $query->withOptions($this->options);
            }

            $query->stream(function (Message $message) use ($callback) {
                $this->messages[] = $message;
                $callback($message);
            });

            $this->analyzeResult();
        } catch (\Exception $e) {
            $this->success = false;
            $this->error = $e->getMessage();
        }

        return $this;
    }

    /**
     * Get the task result as text
     */
    public function getResult(): ?string
    {
        $content = [];

        foreach ($this->messages as $message) {
            if ($message->type === 'assistant') {
                $text = $message->getTextContent();
                if ($text) {
                    $content[] = $text;
                }
            }
        }

        return implode("\n", $content) ?: null;
    }

    /**
     * Get all messages
     *
     * @return array<Message>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Get messages as a collection
     */
    public function getMessagesCollection(): Collection
    {
        return collect($this->messages);
    }

    /**
     * Check if the task was successful
     */
    public function isSuccessful(): bool
    {
        return $this->success;
    }

    /**
     * Get the error message if the task failed
     */
    public function getError(): ?string
    {
        return $this->error;
    }

    /**
     * Get tool uses from the messages
     */
    public function getToolUses(): array
    {
        $toolUses = [];

        foreach ($this->messages as $message) {
            if ($message->hasToolUse()) {
                $toolUses = array_merge($toolUses, $message->getToolUses());
            }
        }

        return $toolUses;
    }

    /**
     * Check if any tools were used
     */
    public function hasUsedTools(): bool
    {
        return ! empty($this->getToolUses());
    }

    /**
     * Get a list of unique tools that were used
     */
    public function getUsedToolNames(): array
    {
        $toolNames = [];

        foreach ($this->getToolUses() as $toolUse) {
            if (isset($toolUse['name'])) {
                $toolNames[] = $toolUse['name'];
            }
        }

        return array_unique($toolNames);
    }

    protected function analyzeResult(): void
    {
        $lastMessage = end($this->messages);

        if ($lastMessage && $lastMessage->type === 'result') {
            $this->success = $lastMessage->success ?? false;
            $this->error = $lastMessage->error ?? null;
        } else {
            $this->success = ! empty($this->messages);
        }
    }
}