<?php

use ArtisanBuild\ClaudeCode\ClaudeCode;
use ArtisanBuild\ClaudeCode\Exceptions\CLINotFoundException;
use ArtisanBuild\ClaudeCode\Messages\AssistantMessage;
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeOptions;
use Illuminate\Support\Facades\Process;

beforeEach(function () {
    Process::fake();
});

it('validates CLI availability on construction', function () {
    Process::shouldReceive('command')
        ->with(['claude', '--version'])
        ->andReturnSelf()
        ->shouldReceive('timeout')
        ->with(5)
        ->andReturnSelf()
        ->shouldReceive('run')
        ->andReturn((object) ['successful' => fn () => false]);

    expect(fn () => new ClaudeCode())
        ->toThrow(CLINotFoundException::class);
});

it('creates a query builder', function () {
    Process::fake([
        'claude --version' => Process::result('Claude CLI version 1.0.0'),
    ]);

    $client = new ClaudeCode();
    $query = $client->query('Test prompt');

    expect($query)->toBeInstanceOf(\ArtisanBuild\ClaudeCode\Support\ClaudeCodeQuery::class);
    expect($query->getPrompt())->toBe('Test prompt');
});

it('executes a query with proper command structure', function () {
    Process::fake([
        'claude --version' => Process::result('Claude CLI version 1.0.0'),
        'claude code *' => Process::result(json_encode([
            'id' => 'msg_123',
            'type' => 'assistant',
            'content' => [
                ['type' => 'text', 'text' => 'Hello from Claude'],
            ],
        ])),
    ]);

    $client = new ClaudeCode();
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

it('applies options to the command', function () {
    Process::fake([
        'claude --version' => Process::result('Claude CLI version 1.0.0'),
        'claude code *' => Process::result(''),
    ]);

    $options = ClaudeCodeOptions::create()
        ->systemPrompt('Be helpful')
        ->maxTurns(5)
        ->model('claude-3-5-sonnet-20241022')
        ->permissionMode('auto')
        ->allowedTools(['Read', 'Write']);

    $client = new ClaudeCode();
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

it('sets working directory from options', function () {
    Process::fake([
        'claude --version' => Process::result('Claude CLI version 1.0.0'),
        'claude code *' => Process::result(''),
    ]);

    $client = new ClaudeCode();
    $client->query('Test')
        ->withWorkingDirectory('/custom/path')
        ->execute();

    Process::assertRan(function ($process) {
        return $process->path === '/custom/path';
    });
});

it('passes API key as environment variable', function () {
    config(['claude-code.api_key' => 'test-api-key']);

    Process::fake([
        'claude --version' => Process::result('Claude CLI version 1.0.0'),
        'claude code *' => Process::result(''),
    ]);

    $client = new ClaudeCode();
    $client->query('Test')->execute();

    Process::assertRan(function ($process) {
        return isset($process->env['ANTHROPIC_API_KEY'])
            && $process->env['ANTHROPIC_API_KEY'] === 'test-api-key';
    });
});

it('handles streaming responses', function () {
    Process::fake([
        'claude --version' => Process::result('Claude CLI version 1.0.0'),
    ]);

    $receivedMessages = [];

    Process::shouldReceive('command')
        ->andReturnSelf()
        ->shouldReceive('path')
        ->andReturnSelf()
        ->shouldReceive('timeout')
        ->andReturnSelf()
        ->shouldReceive('env')
        ->andReturnSelf()
        ->shouldReceive('run')
        ->with(\Mockery::type('callable'))
        ->andReturnUsing(function ($callback) {
            $callback('out', json_encode(['type' => 'assistant', 'content' => [['type' => 'text', 'text' => 'Part 1']]]));
            $callback('out', json_encode(['type' => 'assistant', 'content' => [['type' => 'text', 'text' => 'Part 2']]]));

            return (object) ['successful' => fn () => true];
        });

    $client = new ClaudeCode();
    $client->query('Test')->stream(function ($message) use (&$receivedMessages) {
        $receivedMessages[] = $message;
    });

    expect($receivedMessages)->toHaveCount(2);
    expect($receivedMessages[0]->getTextContent())->toBe('Part 1');
    expect($receivedMessages[1]->getTextContent())->toBe('Part 2');
});