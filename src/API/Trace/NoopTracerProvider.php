<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

class NoopTracerProvider implements TracerProviderInterface
{
    public function getTracer(
        string $name,
        ?string $version = null,
        ?string $schemaUrl = null,
        iterable $attributes = [],
    ): TracerInterface {
        return NoopTracer::getInstance();
    }
}
