<?php

namespace ArtisanBuild\ClaudeCode\Support;

class ClaudeCodeTools
{
    // File system tools
    public const READ = 'Read';

    public const WRITE = 'Write';

    public const EDIT = 'Edit';

    public const MULTI_EDIT = 'MultiEdit';

    public const GLOB = 'Glob';

    public const GREP = 'Grep';

    public const LS = 'LS';

    // Command execution
    public const BASH = 'Bash';

    // Notebook tools
    public const NOTEBOOK_READ = 'NotebookRead';

    public const NOTEBOOK_EDIT = 'NotebookEdit';

    // Web tools
    public const WEB_FETCH = 'WebFetch';

    public const WEB_SEARCH = 'WebSearch';

    // Task management
    public const TODO_READ = 'TodoRead';

    public const TODO_WRITE = 'TodoWrite';

    // Agent tool
    public const TASK = 'Task';

    // Common tool sets
    public static function fileTools(): array
    {
        return [
            self::READ,
            self::WRITE,
            self::EDIT,
            self::MULTI_EDIT,
            self::GLOB,
            self::GREP,
            self::LS,
        ];
    }

    public static function readOnlyTools(): array
    {
        return [
            self::READ,
            self::GLOB,
            self::GREP,
            self::LS,
            self::WEB_FETCH,
            self::WEB_SEARCH,
            self::TODO_READ,
        ];
    }

    public static function writeTools(): array
    {
        return [
            self::WRITE,
            self::EDIT,
            self::MULTI_EDIT,
            self::NOTEBOOK_EDIT,
            self::TODO_WRITE,
        ];
    }

    public static function webTools(): array
    {
        return [
            self::WEB_FETCH,
            self::WEB_SEARCH,
        ];
    }

    public static function notebookTools(): array
    {
        return [
            self::NOTEBOOK_READ,
            self::NOTEBOOK_EDIT,
        ];
    }

    public static function allTools(): array
    {
        return [
            self::READ,
            self::WRITE,
            self::EDIT,
            self::MULTI_EDIT,
            self::GLOB,
            self::GREP,
            self::LS,
            self::BASH,
            self::NOTEBOOK_READ,
            self::NOTEBOOK_EDIT,
            self::WEB_FETCH,
            self::WEB_SEARCH,
            self::TODO_READ,
            self::TODO_WRITE,
            self::TASK,
        ];
    }
}
