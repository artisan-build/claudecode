<?php

namespace ArtisanBuild\ClaudeCode\Support;

use ArtisanBuild\ClaudeCode\ClaudeCode;
use ArtisanBuild\ClaudeCode\Messages\Message;

class ClaudeCodeSession
{
    protected array $messages = [];

    protected int $turnCount = 0;

    public function __construct(protected ClaudeCode $client, protected ?ClaudeCodeOptions $options = null) {}

    /**
     * Send a prompt and get the response
     *
     * @return array<Message>
     */
    public function prompt(string $prompt): array
    {
        $query = $this->client->query($prompt);

        if ($this->options) {
            $query->withOptions($this->options);
        }

        $response = $query->execute();
        $this->messages = array_merge($this->messages, $response);
        $this->turnCount++;

        return $response;
    }

    /**
     * Stream a prompt and handle messages as they arrive
     */
    public function stream(string $prompt, callable $callback): void
    {
        $query = $this->client->query($prompt);

        if ($this->options) {
            $query->withOptions($this->options);
        }

        $query->stream(function (Message $message) use ($callback): void {
            $this->messages[] = $message;
            $callback($message);
        });

        $this->turnCount++;
    }

    /**
     * Get all messages in the session
     *
     * @return array<Message>
     */
    public function getMessages(): array
    {
        return $this->messages;
    }

    /**
     * Get only assistant messages
     *
     * @return array<Message>
     */
    public function getAssistantMessages(): array
    {
        return array_filter($this->messages, fn (Message $message) => $message->type === 'assistant');
    }

    /**
     * Get the last assistant response as text
     */
    public function getLastResponse(): ?string
    {
        $assistantMessages = $this->getAssistantMessages();
        if (empty($assistantMessages)) {
            return null;
        }

        $lastMessage = end($assistantMessages);

        return $lastMessage->getTextContent();
    }

    /**
     * Get the number of turns in the session
     */
    public function getTurnCount(): int
    {
        return $this->turnCount;
    }

    /**
     * Clear the session
     */
    public function clear(): void
    {
        $this->messages = [];
        $this->turnCount = 0;
    }

    /**
     * Set session options
     */
    public function setOptions(ClaudeCodeOptions $options): self
    {
        $this->options = $options;

        return $this;
    }
}
