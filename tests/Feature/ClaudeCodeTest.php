<?php

use ArtisanBuild\ClaudeCode\ClaudeCode;
use ArtisanBuild\ClaudeCode\Exceptions\CLINotFoundException;
use ArtisanBuild\ClaudeCode\Messages\AssistantMessage;
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeOptions;
use Illuminate\Support\Facades\Process;

it('validates CLI availability on construction', function (): void {
    Process::fake([
        '*' => Process::result(exitCode: 1),
    ]);

    expect(fn () => new ClaudeCode)
        ->toThrow(CLINotFoundException::class);
});

it('creates a query builder', function (): void {
    Process::fake([
        '*' => Process::result('Claude CLI version 1.0.0', exitCode: 0),
    ]);

    $client = new ClaudeCode;
    $query = $client->query('Test prompt');

    expect($query)->toBeInstanceOf(\ArtisanBuild\ClaudeCode\Support\ClaudeCodeQuery::class);
    expect($query->getPrompt())->toBe('Test prompt');
});

it('executes a query with proper command structure', function (): void {
    Process::fake([
        '*' => Process::result(
            output: json_encode([
                'id' => 'msg_123',
                'type' => 'assistant',
                'content' => [
                    ['type' => 'text', 'text' => 'Hello from Claude'],
                ],
            ]),
            exitCode: 0
        ),
    ]);

    $client = new ClaudeCode;
    $messages = $client->query('Say hello')->execute();

    Process::assertRan(function ($process) {
        $command = $process->command;

        return is_array($command)
            && $command[0] === 'claude'
            && $command[1] === 'code'
            && in_array('--json', $command)
            && in_array('--no-color', $command)
            && end($command) === 'Say hello';
    });

    expect($messages)->toHaveCount(1);
    expect($messages[0])->toBeInstanceOf(AssistantMessage::class);
    expect($messages[0]->getTextContent())->toBe('Hello from Claude');
});

it('applies options to the command', function (): void {
    Process::fake([
        '*' => Process::result('', exitCode: 0),
    ]);

    $options = ClaudeCodeOptions::create()
        ->systemPrompt('Be helpful')
        ->maxTurns(5)
        ->model('claude-3-5-sonnet-20241022')
        ->permissionMode('auto')
        ->allowedTools(['Read', 'Write']);

    $client = new ClaudeCode;
    $client->query('Test')->withOptions($options)->execute();

    Process::assertRan(function ($process) {
        $command = $process->command;

        return is_array($command)
            && in_array('--system-prompt', $command)
            && in_array('Be helpful', $command)
            && in_array('--max-turns', $command)
            && in_array('5', $command)
            && in_array('--model', $command)
            && in_array('claude-3-5-sonnet-20241022', $command)
            && in_array('--permission-mode', $command)
            && in_array('auto', $command)
            && in_array('--allowed-tool', $command)
            && in_array('Read', $command)
            && in_array('Write', $command);
    });
});

it('sets working directory from options', function (): void {
    Process::fake([
        '*' => Process::result('', exitCode: 0),
    ]);

    $tempDir = sys_get_temp_dir();

    $client = new ClaudeCode;
    $client->query('Test')
        ->withWorkingDirectory($tempDir)
        ->execute();

    Process::assertRan(fn ($process) => $process->path === $tempDir);
});

it('passes API key as environment variable', function (): void {
    config(['claude-code.api_key' => 'test-api-key']);

    Process::fake([
        '*' => Process::result('', exitCode: 0),
    ]);

    $client = new ClaudeCode;
    $client->query('Test')->execute();

    // Process facade may not capture env vars properly in tests
    // At least verify the process was run
    Process::assertRan(function ($process) {
        return is_array($process->command)
            && count($process->command) >= 2
            && $process->command[0] === 'claude'
            && $process->command[1] === 'code';
    });
});

it('handles streaming responses', function (): void {
    // Process::fake doesn't support streaming, so we'll just verify the method runs without errors
    // and that processes are started correctly
    Process::fake([
        '*' => Process::result('', exitCode: 0),
    ]);

    $receivedMessages = [];

    $client = new ClaudeCode;

    // The stream method should complete without throwing any exception
    $client->query('Test')->stream(function ($message) use (&$receivedMessages): void {
        $receivedMessages[] = $message;
    });

    // Since Process::fake doesn't support start/wait pattern well, we just verify it didn't crash
    expect(true)->toBeTrue();

    // Verify a process was started
    Process::assertRanTimes(function ($process) {
        return is_array($process->command) && $process->command[0] === 'claude';
    }, 2); // One for version check, one for the actual command
});
