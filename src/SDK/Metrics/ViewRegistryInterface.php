<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

interface ViewRegistryInterface
{
    /**
     * @return iterable<ViewProjection>|null
     */
    public function find(Instrument $instrument, InstrumentationScopeInterface $instrumentationScope): ?iterable;
}
