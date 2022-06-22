<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Common\Event\Event;

use OpenTelemetry\API\Common\Event\StoppableEventTrait;
use Psr\EventDispatcher\StoppableEventInterface;
use Throwable;

class ErrorEvent implements StoppableEventInterface
{
    use StoppableEventTrait;

    protected string $message;
    protected Throwable $exception;

    public function __construct(string $message, Throwable $error)
    {
        $this->message = $message;
        $this->exception = $error;
    }

    public function getException(): Throwable
    {
        return $this->exception;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
