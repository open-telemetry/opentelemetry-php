<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use Closure;
use OpenTelemetry\Context\ContextInterface;

class LateBindingLogger implements LoggerInterface
{
    private ?LoggerInterface $logger = null;

    /** @param Closure(): LoggerInterface $factory */
    public function __construct(
        private readonly Closure $factory,
    ) {
    }

    public function emit(LogRecord $logRecord): void
    {
        ($this->logger ??= ($this->factory)())->emit($logRecord);
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function emitEvent(string $name, ?int $timestamp = null, ?int $observerTimestamp = null, ?ContextInterface $context = null, ?Severity $severityNumber = null, ?string $severityText = null, mixed $body = null, iterable $attributes = []): void
    {
        ($this->logger ??= ($this->factory)())->emitEvent(
            $name,
            $timestamp,
            $observerTimestamp,
            $context,
            $severityNumber,
            $severityText,
            $body,
            $attributes
        );
    }
}
