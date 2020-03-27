<?php

declare(strict_types=1);

namespace OpenTelemetry\Trace;

interface TracerProvider
{
    public function getTracer(string $name, ?string $version = null): Tracer;
}
