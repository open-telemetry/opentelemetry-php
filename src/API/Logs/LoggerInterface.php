<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

use OpenTelemetry\Context\ContextInterface;

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
     * Determine if the logger is enabled. Instrumentation authors SHOULD call this method each time they
     * emit a LogRecord, to ensure they have the most up-to-date response.
     */
    public function isEnabled(?ContextInterface $context = null, ?int $severityNumber = null, ?string $eventName = null): bool;
}
