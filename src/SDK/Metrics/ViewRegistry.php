<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\InstrumentationScope;

interface ViewRegistry {

    /**
     * @return iterable<ViewProjection>
     */
    public function find(Instrument $instrument, InstrumentationScope $instrumentationScope): iterable;
}
