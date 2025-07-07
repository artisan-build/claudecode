<?php

namespace ArtisanBuild\ClaudeCode\Messages;

use ArtisanBuild\ClaudeCode\Exceptions\ClaudeCodeException;

class MessageFactory
{
    public static function create(array $data): Message
    {
        $type = $data['type'] ?? 'unknown';

        return match ($type) {
            'assistant' => new AssistantMessage($data),
            'user' => new UserMessage($data),
            'system' => new SystemMessage($data),
            'result' => new ResultMessage($data),
            default => throw new ClaudeCodeException("Unknown message type: {$type}"),
        };
    }
}
