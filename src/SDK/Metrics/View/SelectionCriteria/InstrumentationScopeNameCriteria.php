<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteriaInterface;

final class InstrumentationScopeNameCriteria implements SelectionCriteriaInterface
{
    public function __construct(private readonly string $name)
    {
    }

    #[\Override]
    public function accepts(Instrument $instrument, InstrumentationScopeInterface $instrumentationScope): bool
    {
        return $this->name === $instrumentationScope->getName();
    }
}
