<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

final class InstrumentationScopeNameCriteria implements SelectionCriteria
{
    private string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function accepts(Instrument $instrument, InstrumentationScope $instrumentationScope): bool
    {
        return $this->name === $instrumentationScope->getName();
    }
}
