<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace;

interface TracerProviderInterface
{
    public function getTracer(string $name, ?string $version = null): TracerInterface;
}
