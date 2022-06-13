<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Event\Event;

class DebugEvent
{
    private string $message;
    private array $extra;

    public function __construct(string $message, array $extra = [])
    {
        $this->message = $message;
        $this->extra = $extra;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getExtra(): array
    {
        return $this->extra;
    }
}
