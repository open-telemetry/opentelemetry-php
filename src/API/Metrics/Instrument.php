<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Metrics;

interface Instrument
{
    /**
     * Determine if the instrument is enabled. Instrumentation authors SHOULD call this API each time they record a measurement.
     *
     * MUST return false if:
     *  - The MeterConfig of the Meter used to create the instrument has parameter disabled=true
     *  - All resolved views for the instrument are configured with the Drop Aggregation
     * @experimental
     * @see https://opentelemetry.io/docs/specs/otel/metrics/sdk/#instrument-enabled
     */
    public function isEnabled(): bool;
}
