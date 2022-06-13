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
    protected ?Throwable $error = null;

    public function __construct(string $message, ?Throwable $error)
    {
        $this->message = $message;
        $this->error = $error;
    }

    public function getError(): ?Throwable
    {
        return $this->error;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
