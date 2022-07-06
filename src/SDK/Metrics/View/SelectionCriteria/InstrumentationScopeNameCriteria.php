<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

final class InstrumentationScopeNameCriteria implements SelectionCriteria
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function accepts(Instrument $instrument, InstrumentationScopeInterface $instrumentationScope): bool
    {
        return $this->name === $instrumentationScope->getName();
    }
}
