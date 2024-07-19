<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\InstrumentationScope\Condition;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;
use OpenTelemetry\SDK\Common\InstrumentationScope\State;

class TracerConfiguratorBuilder
{
    /** @var list<Condition> */
    private array $conditions = [];

    public function addCondition(Predicate $predicate, State $state): TracerConfiguratorBuilder
    {
        $this->conditions[] = new Condition($predicate, $state);

        return $this;
    }

    public function build(): TracerConfigurator
    {
        return new TracerConfigurator($this->conditions);
    }
}
