<?php

declare(strict_types=1);

namespace ArtisanBuild\ClaudeCode\Messages;

class SystemMessage extends Message
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->type = 'system';
    }
}
