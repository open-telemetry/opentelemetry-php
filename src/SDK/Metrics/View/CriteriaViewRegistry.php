<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\View;

use OpenTelemetry\SDK\InstrumentationScope;
use OpenTelemetry\SDK\Metrics\Instrument;
use OpenTelemetry\SDK\Metrics\ViewRegistry;

final class CriteriaViewRegistry implements ViewRegistry
{

    /** @var list<SelectionCriteria> */
    private array $criteria = [];
    /** @var list<ViewTemplate> */
    private array $views = [];

    public function register(SelectionCriteria $criteria, ViewTemplate $view): void
    {
        $this->criteria[] = $criteria;
        $this->views[] = $view;
    }

    public function find(Instrument $instrument, InstrumentationScope $instrumentationScope): iterable
    {
        foreach ($this->criteria as $i => $criteria) {
            if ($criteria->accepts($instrument, $instrumentationScope)) {
                yield $this->views[$i]->project($instrument);
            }
        }
    }
}
