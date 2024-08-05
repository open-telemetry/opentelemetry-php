<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface Instrument
{
    /**
     * Determine if the instrument is enabled. Instrumentation authors SHOULD call this API each time they record a measurement.
     * @experimental
     */
    public function enabled(): bool;
}
