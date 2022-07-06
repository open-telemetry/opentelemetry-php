<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

interface ViewRegistry
{

    /**
     * @return iterable<ViewProjection>
     */
    public function find(Instrument $instrument, InstrumentationScopeInterface $instrumentationScope): iterable;
}
