<?php

namespace ArtisanBuild\ClaudeCode\Messages;

abstract class Message
{
    public string $id;

    public string $type;

    public array $content = [];

    public array $metadata = [];

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? uniqid('msg_');
        $this->type = $data['type'] ?? 'unknown';
        $this->content = $data['content'] ?? [];
        $this->metadata = $data['metadata'] ?? [];
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'content' => $this->content,
            'metadata' => $this->metadata,
        ];
    }

    public function getTextContent(): ?string
    {
        $text = [];

        foreach ($this->content as $block) {
            if (isset($block['type']) && $block['type'] === 'text' && isset($block['text'])) {
                $text[] = $block['text'];
            }
        }

        return implode("\n", $text) ?: null;
    }

    public function hasToolUse(): bool
    {
        foreach ($this->content as $block) {
            if (isset($block['type']) && $block['type'] === 'tool_use') {
                return true;
            }
        }

        return false;
    }

    public function getToolUses(): array
    {
        $tools = [];

        foreach ($this->content as $block) {
            if (isset($block['type']) && $block['type'] === 'tool_use') {
                $tools[] = $block;
            }
        }

        return $tools;
    }
}
