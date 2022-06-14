<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\Event\Event;

use OpenTelemetry\SDK\Common\Event\StoppableEventTrait;
use Psr\EventDispatcher\StoppableEventInterface;
use Throwable;

class WarningEvent implements StoppableEventInterface
{
    use StoppableEventTrait;

    protected string $message;
    protected ?Throwable $exception = null;

    public function __construct(string $message, ?Throwable $exception = null)
    {
        $this->message = $message;
        $this->exception = $exception;
    }

    public function getException(): ?Throwable
    {
        return $this->exception;
    }

    public function hasError(): bool
    {
        return $this->exception !== null;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
