<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface Instrument
{
    /**
     * Instrumentation authors SHOULD to call this API each time they record a measurement.
     * @experimental
     */
    public function enabled(): bool;
}
