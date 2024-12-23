<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteriaInterface;

final readonly class AllCriteria implements SelectionCriteriaInterface
{
    /**
     * @param iterable<SelectionCriteriaInterface> $criteria
     */
    public function __construct(private iterable $criteria)
    {
    }

    public function accepts(Instrument $instrument, InstrumentationScopeInterface $instrumentationScope): bool
    {
        foreach ($this->criteria as $criterion) {
            if (!$criterion->accepts($instrument, $instrumentationScope)) {
                return false;
            }
        }

        return true;
    }
}
