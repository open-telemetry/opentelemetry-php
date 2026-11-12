<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\Context\ContextInterface;

/**
 * Implementations MUST be safe for concurrent use, for example from multiple fibers.
 */
interface LoggerInterface
{
    /**
     * Deprecated, use {@link LoggerInterface::logRecordBuilder()} instead.
     *
     * Deprecated:
     * ```
     * $logger->emit(new LogRecord()
     *     ->setTimestamp($timestamp)
     *     ...
     *     ->setEventName($eventName)
     * );
     * ```
     *
     * Instead, use:
     * ```
     * $logger->logRecordBuilder()
     *     ->setTimestamp($timestamp)
     *     ...
     *     ->setEventName($eventName)
     *     ->emit();
     * ```
     */
    public function emit(LogRecord $logRecord): void;

    public function logRecordBuilder(): LogRecordBuilderInterface;

    /**
     * Determine if the logger is enabled for the given arguments. If no context is given, the current
     * context is used.
     *
     * Calling this method is optional, and is not required before emitting a LogRecord. It is a performance
     * optimization for when building the LogRecord is expensive, for example when its body or attributes
     * come from a database query. Call this first and skip that work if it returns false. If building the
     * LogRecord is cheap, emit it directly.
     *
     * The result can change over time, so a cached value can become stale.
     *
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/logs/api.md#enabled
     */
    public function isEnabled(?ContextInterface $context = null, ?int $severityNumber = null, ?string $eventName = null): bool;
}
