<p align="center"><img src="https://github.com/artisan-build/claudecode/raw/HEAD/art/claudecode.png" width="75%" alt="Artisan Build Package Claude Code Logo"></p>

# Claude Code SDK for Laravel

A Laravel package that provides a seamless integration with Claude Code, allowing you to execute AI-powered coding tasks directly from your Laravel application.

> [!WARNING]  
> This package is currently under active development, and we have not yet released a major version. Once a 0.* version
> has been tagged, we strongly recommend locking your application to a specific working version because we might make
> breaking changes even in patch releases until we've tagged 1.0.

## Requirements

- PHP 8.3+
- Laravel 11.0+
- Claude CLI installed (`claude` command available)
- Anthropic API key

## Installation

```bash
composer require artisan-build/claudecode
```

## Configuration

Publish the configuration file:

```bash
php artisan vendor:publish --tag=claude-code-config
```

Set your Anthropic API key in your `.env` file:

```env
ANTHROPIC_API_KEY=your-api-key-here
```

Additional configuration options:

```env
CLAUDE_CLI_PATH=claude
CLAUDE_TIMEOUT=120
CLAUDE_MODEL=claude-3-5-sonnet-20241022
CLAUDE_PERMISSION_MODE=auto
CLAUDE_ALLOWED_TOOLS=Read,Write,Edit
```

## Usage

### Basic Usage

```php
use ArtisanBuild\ClaudeCode\Facades\ClaudeCode;

// Simple query
$response = ClaudeCode::query('Write a function to calculate factorial')
    ->get();

// With options
$response = ClaudeCode::query('Analyze this codebase and suggest improvements')
    ->withModel('claude-3-5-sonnet-20241022')
    ->withWorkingDirectory('/path/to/project')
    ->allowTools(['Read', 'Grep', 'Glob'])
    ->execute();
```

### Using the Task Helper

```php
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeTask;

$task = ClaudeCodeTask::create('Refactor the UserController')
    ->inDirectory(base_path('app/Http/Controllers'))
    ->allowTools(['Read', 'Edit', 'Write'])
    ->run();

if ($task->isSuccessful()) {
    echo $task->getResult();
} else {
    echo "Error: " . $task->getError();
}
```

### Streaming Responses

```php
ClaudeCode::query('Build a REST API for managing books')
    ->stream(function ($message) {
        echo $message->getTextContent() . "\n";
    });
```

### Session Management

```php
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeSession;

$session = new ClaudeCodeSession(app(ClaudeCode::class));

// First prompt
$session->prompt('Create a new Laravel model for Product');

// Follow-up prompt (maintains context)
$session->prompt('Now create a migration for this model');

// Get all responses
$allMessages = $session->getMessages();
$lastResponse = $session->getLastResponse();
```

### Tool Management

```php
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeTools;

// Use predefined tool sets
$response = ClaudeCode::query('Analyze the codebase')
    ->allowTools(ClaudeCodeTools::readOnlyTools())
    ->execute();

// Check what tools were used
$task = ClaudeCodeTask::create('Update all tests')
    ->allowTools(ClaudeCodeTools::fileTools())
    ->run();

if ($task->hasUsedTools()) {
    $toolNames = $task->getUsedToolNames(); // ['Read', 'Edit', 'Write']
}
```

### Advanced Options

```php
use ArtisanBuild\ClaudeCode\Support\ClaudeCodeOptions;

$options = ClaudeCodeOptions::create()
    ->systemPrompt('You are a Laravel expert. Follow PSR-12 standards.')
    ->maxTurns(10)
    ->model('claude-3-5-sonnet-20241022')
    ->permissionMode('auto')
    ->workingDirectory(base_path())
    ->allowedTools(['Read', 'Write', 'Edit', 'Bash']);

$response = ClaudeCode::query('Implement user authentication')
    ->withOptions($options)
    ->execute();
```

## Memberware

This package is part of our internal toolkit and is optimized for our own purposes. We do not accept issues or PRs
in this repository. 

