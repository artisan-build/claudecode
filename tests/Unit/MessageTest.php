<?php

use ArtisanBuild\ClaudeCode\Messages\AssistantMessage;
use ArtisanBuild\ClaudeCode\Messages\MessageFactory;
use ArtisanBuild\ClaudeCode\Messages\ResultMessage;

it('creates messages from factory', function (): void {
    $assistantData = [
        'type' => 'assistant',
        'id' => 'msg_123',
        'content' => [
            ['type' => 'text', 'text' => 'Hello'],
        ],
    ];

    $message = MessageFactory::create($assistantData);

    expect($message)->toBeInstanceOf(AssistantMessage::class);
    expect($message->type)->toBe('assistant');
    expect($message->id)->toBe('msg_123');
});

it('extracts text content from messages', function (): void {
    $message = new AssistantMessage([
        'content' => [
            ['type' => 'text', 'text' => 'Part 1'],
            ['type' => 'tool_use', 'name' => 'Read'],
            ['type' => 'text', 'text' => 'Part 2'],
        ],
    ]);

    expect($message->getTextContent())->toBe("Part 1\nPart 2");
});

it('detects tool usage in messages', function (): void {
    $message = new AssistantMessage([
        'content' => [
            ['type' => 'text', 'text' => 'Let me read that file'],
            ['type' => 'tool_use', 'name' => 'Read', 'input' => ['file' => 'test.php']],
        ],
    ]);

    expect($message->hasToolUse())->toBeTrue();
    expect($message->getToolUses())->toHaveCount(1);
    expect($message->getToolUses()[0]['name'])->toBe('Read');
});

it('handles result messages with success and error states', function (): void {
    $successResult = new ResultMessage([
        'success' => true,
        'content' => [['type' => 'text', 'text' => 'Task completed']],
    ]);

    $errorResult = new ResultMessage([
        'success' => false,
        'error' => 'Task failed',
        'exit_code' => 1,
    ]);

    expect($successResult->success)->toBeTrue();
    expect($successResult->error)->toBeNull();

    expect($errorResult->success)->toBeFalse();
    expect($errorResult->error)->toBe('Task failed');
    expect($errorResult->exitCode)->toBe(1);
});
