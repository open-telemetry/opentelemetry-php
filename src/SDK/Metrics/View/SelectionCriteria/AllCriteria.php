<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\View\SelectionCriteria;

final class AllCriteria implements SelectionCriteria
{
    private iterable $criteria;

    /**
     * @param iterable<SelectionCriteria> $criteria
     */
    public function __construct(iterable $criteria)
    {
        $this->criteria = $criteria;
    }

    public function accepts(Instrument $instrument, InstrumentationScope $instrumentationScope): bool
    {
        foreach ($this->criteria as $criterion) {
            if (!$criterion->accepts($instrument, $instrumentationScope)) {
                return false;
            }
        }

        return true;
    }
}
