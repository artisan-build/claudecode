<?php

use ArtisanBuild\ClaudeCode\Support\ClaudeCodeOptions;

it('creates options with fluent interface', function (): void {
    $options = ClaudeCodeOptions::create()
        ->systemPrompt('Be helpful')
        ->maxTurns(10)
        ->model('claude-3-5-sonnet-20241022')
        ->permissionMode('auto')
        ->workingDirectory('/path/to/project')
        ->allowedTools(['Read', 'Write']);

    expect($options->systemPrompt)->toBe('Be helpful');
    expect($options->maxTurns)->toBe(10);
    expect($options->model)->toBe('claude-3-5-sonnet-20241022');
    expect($options->permissionMode)->toBe('auto');
    expect($options->workingDirectory)->toBe('/path/to/project');
    expect($options->allowedTools)->toBe(['Read', 'Write']);
});

it('converts options to array filtering null values', function (): void {
    $options = ClaudeCodeOptions::create()
        ->systemPrompt('Be helpful')
        ->maxTurns(5);

    $array = $options->toArray();

    expect($array)->toBe([
        'system_prompt' => 'Be helpful',
        'max_turns' => 5,
    ]);
    expect($array)->not->toHaveKey('model');
    expect($array)->not->toHaveKey('allowed_tools');
});
