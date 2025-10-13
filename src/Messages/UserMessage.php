<?php

declare(strict_types=1);

namespace ArtisanBuild\ClaudeCode\Messages;

class UserMessage extends Message
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->type = 'user';
    }
}
