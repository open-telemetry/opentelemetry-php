<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/event-api.md#events-api-interface
 */
interface EventLoggerInterface
{
    public function logEvent(string $eventName, LogRecord $logRecord): void;
}
