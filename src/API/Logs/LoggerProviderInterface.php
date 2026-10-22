<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

/**
 * Implementations MUST be safe for concurrent use, for example from multiple fibers.
 *
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.61.0/specification/logs/api.md#get-a-logger
 */
interface LoggerProviderInterface
{
    public function getLogger(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = [], //instrumentation scope attributes
    ): LoggerInterface;
}
