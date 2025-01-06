<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

/**
 * @deprecated
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.32.0/specification/logs/event-api.md#get-an-eventlogger
 */
interface EventLoggerProviderInterface
{
    public function getEventLogger(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = [],
    ): EventLoggerInterface;
}
