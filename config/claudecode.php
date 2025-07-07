<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Claude CLI Path
    |--------------------------------------------------------------------------
    |
    | The path to the Claude CLI executable. By default, it assumes 'claude'
    | is available in your system PATH. You can specify a full path here.
    |
    */
    'cli_path' => env('CLAUDE_CLI_PATH', 'claude'),

    /*
    |--------------------------------------------------------------------------
    | API Key
    |--------------------------------------------------------------------------
    |
    | Your Anthropic API key. This will be passed to the Claude CLI as an
    | environment variable when executing commands.
    |
    */
    'api_key' => env('ANTHROPIC_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Timeout
    |--------------------------------------------------------------------------
    |
    | The default timeout in seconds for Claude Code operations. This can be
    | overridden on a per-query basis.
    |
    */
    'timeout' => env('CLAUDE_TIMEOUT', 120),

    /*
    |--------------------------------------------------------------------------
    | Default Options
    |--------------------------------------------------------------------------
    |
    | Default options that will be applied to all queries unless overridden.
    | Set any value to null to disable that default option.
    |
    */
    'default_options' => [
        'model' => env('CLAUDE_MODEL', null), // e.g., 'claude-3-5-sonnet-20241022'
        'maxTurns' => env('CLAUDE_MAX_TURNS', null),
        'systemPrompt' => env('CLAUDE_SYSTEM_PROMPT', null),
        'permissionMode' => env('CLAUDE_PERMISSION_MODE', null), // 'auto', 'allow', 'confirm', 'deny'
        'workingDirectory' => env('CLAUDE_WORKING_DIRECTORY', null),
        'allowedTools' => env('CLAUDE_ALLOWED_TOOLS') ? explode(',', (string) env('CLAUDE_ALLOWED_TOOLS')) : null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Provider Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for alternative providers like Amazon Bedrock or Google
    | Vertex AI. The Claude CLI will use these if configured.
    |
    */
    'provider' => env('CLAUDE_PROVIDER', 'anthropic'), // 'anthropic', 'bedrock', 'vertex'

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Configure logging for Claude Code operations. This can be useful for
    | debugging and monitoring usage.
    |
    */
    'logging' => [
        'enabled' => env('CLAUDE_LOGGING_ENABLED', false),
        'channel' => env('CLAUDE_LOG_CHANNEL', 'stack'),
    ],
];
