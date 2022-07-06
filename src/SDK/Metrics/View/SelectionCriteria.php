<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Instrument;

interface SelectionCriteria
{
    public function accepts(Instrument $instrument, InstrumentationScopeInterface $instrumentationScope): bool;
}
