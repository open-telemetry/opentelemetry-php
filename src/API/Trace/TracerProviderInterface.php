<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

/** @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/api.md#tracerprovider */
interface TracerProviderInterface
{
    /**
     * @see https://github.com/open-telemetry/opentelemetry-specification/blob/v1.7.0/specification/trace/api.md#get-a-tracer
     */
    public function getTracer(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = []
    ): TracerInterface;
}
