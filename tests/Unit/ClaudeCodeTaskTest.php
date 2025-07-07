<?php

use ArtisanBuild\ClaudeCode\Facades\ClaudeCode;
use ArtisanBuild\ClaudeCode\Messages\AssistantMessage;
use ArtisanBuild\ClaudeCode\Messages\ResultMessage;
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeQuery;
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeTask;
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeTools;

it('creates and executes a task', function (): void {
    $mockMessages = [
        new AssistantMessage([
            'content' => [['type' => 'text', 'text' => 'Task completed successfully']],
        ]),
        new ResultMessage(['success' => true]),
    ];

    $mockQuery = Mockery::mock(ClaudeCodeQuery::class);
    $mockQuery->shouldReceive('withOptions')->andReturnSelf();
    $mockQuery->shouldReceive('execute')->andReturn($mockMessages);

    ClaudeCode::shouldReceive('query')->andReturn($mockQuery);

    $task = ClaudeCodeTask::create('Write a test')
        ->withModel('claude-3-5-sonnet-20241022')
        ->inDirectory('/project')
        ->allowTools(ClaudeCodeTools::fileTools())
        ->run();

    expect($task->isSuccessful())->toBeTrue();
    expect($task->getResult())->toBe('Task completed successfully');
    expect($task->getError())->toBeNull();
});

it('handles task failures', function (): void {
    $mockQuery = Mockery::mock(ClaudeCodeQuery::class);
    $mockQuery->shouldReceive('withOptions')->andReturnSelf();
    $mockQuery->shouldReceive('execute')->andThrow(new Exception('API error'));

    ClaudeCode::shouldReceive('query')->andReturn($mockQuery);

    $task = ClaudeCodeTask::create('Failing task')->run();

    expect($task->isSuccessful())->toBeFalse();
    expect($task->getError())->toBe('API error');
    expect($task->getResult())->toBeNull();
});

it('extracts tool usage information', function (): void {
    $mockMessages = [
        new AssistantMessage([
            'content' => [
                ['type' => 'text', 'text' => 'Reading file'],
                ['type' => 'tool_use', 'name' => 'Read', 'input' => ['file' => 'test.php']],
                ['type' => 'tool_use', 'name' => 'Write', 'input' => ['file' => 'output.php']],
            ],
        ]),
    ];

    $mockQuery = Mockery::mock(ClaudeCodeQuery::class);
    $mockQuery->shouldReceive('withOptions')->andReturnSelf();
    $mockQuery->shouldReceive('execute')->andReturn($mockMessages);

    ClaudeCode::shouldReceive('query')->andReturn($mockQuery);

    $task = ClaudeCodeTask::create('Process files')->run();

    expect($task->hasUsedTools())->toBeTrue();
    expect($task->getUsedToolNames())->toBe(['Read', 'Write']);
    expect($task->getToolUses())->toHaveCount(2);
});
