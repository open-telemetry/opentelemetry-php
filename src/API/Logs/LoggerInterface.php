<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\Context\ContextInterface;

interface LoggerInterface
{
    /**
     * This method should only be used when implementing a `log appender`
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.32.0/specification/logs/bridge-api.md#artifact-naming
     */
    public function emit(LogRecord $logRecord): void;

    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.40.0/specification/logs/api.md#emit-an-event
     */
    public function emitEvent(
        string $name,
        ?int $timestamp = null,
        ?int $observerTimestamp = null,
        ?ContextInterface $context = null,
        ?Severity $severityNumber = null,
        ?string $severityText = null,
        mixed $body = null,
        iterable $attributes = [],
    ): void;

    /**
     * Determine if the logger is enabled. Logs bridge API authors SHOULD call this method each time they
     * are about to generate a LogRecord, to avoid performing computationally expensive work.
     * @experimental
     */
    public function isEnabled(): bool;
}
