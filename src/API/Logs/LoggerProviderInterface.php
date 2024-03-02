<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Logs;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/logs/bridge-api.md#get-a-logger
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
