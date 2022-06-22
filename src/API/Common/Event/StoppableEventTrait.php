<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event;

trait StoppableEventTrait
{
    private bool $stopped = false;

    public function isPropagationStopped(): bool
    {
        return $this->stopped;
    }

    public function stopPropagation(): void
    {
        $this->stopped = true;
    }
}
