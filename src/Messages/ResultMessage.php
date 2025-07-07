<?php

namespace ArtisanBuild\ClaudeCode\Messages;

class ResultMessage extends Message
{
    public bool $success;

    public ?string $error;

    public ?int $exitCode;

    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->type = 'result';
        $this->success = $data['success'] ?? false;
        $this->error = $data['error'] ?? null;
        $this->exitCode = $data['exit_code'] ?? null;
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'success' => $this->success,
            'error' => $this->error,
            'exit_code' => $this->exitCode,
        ]);
    }
}