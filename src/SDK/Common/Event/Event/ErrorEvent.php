<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Event\Event;

use OpenTelemetry\SDK\Common\Event\StoppableEventTrait;
use Psr\EventDispatcher\StoppableEventInterface;
use Throwable;

class ErrorEvent implements StoppableEventInterface
{
    use StoppableEventTrait;

    private string $message;
    private Throwable $error;

    public function __construct(string $message, Throwable $error)
    {
        $this->message = $message;
        $this->error = $error;
    }

    public function getError(): Throwable
    {
        return $this->error;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
