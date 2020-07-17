<?php

declare(strict_types=1);

namespace OpenTelemetry\Metrics;

interface MeterProvider
{
    /**
     * Returns a Meter, creating one if one with the given name and version is
     * not already created.
     *
     * @param name The name of the meter or instrumentation library.
     * @param version The version of the meter or instrumentation library.
     * @returns Meter A Meter with the given name and version
     */
    public function getMeter(string $name, ?string $version = null): Meter;
}
