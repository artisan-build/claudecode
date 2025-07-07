<?php

namespace ArtisanBuild\ClaudeCode\Messages;

class AssistantMessage extends Message
{
    public function __construct(array $data)
    {
        parent::__construct($data);
        $this->type = 'assistant';
    }
}