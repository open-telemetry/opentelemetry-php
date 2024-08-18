<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

interface LoggerInterface
{
    /**
     * This method should only be used when implementing a `log appender`
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.32.0/specification/logs/bridge-api.md#artifact-naming
     */
    public function emit(LogRecord $logRecord): void;

    /**
     * Determine if the logger is enabled. Logs bridge API authors SHOULD call this method each time they
     * are about to generate a LogRecord, to avoid performing computationally expensive work.
     * @experimental
     */
    public function isEnabled(): bool;
}
