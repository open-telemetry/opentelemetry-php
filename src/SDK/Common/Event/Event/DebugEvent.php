<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Event\Event;

class DebugEvent
{
    private string $message;

    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
