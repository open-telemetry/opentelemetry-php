<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope;

class ConfiguratorBuilder
{
    /** @var list<Condition> */
    private array $conditions = [];

    public function addCondition(Predicate $predicate, State $state): ConfiguratorBuilder
    {
        $this->conditions[] = new Condition($predicate, $state);

        return $this;
    }

    public function build(): Configurator
    {
        return new Configurator($this->conditions);
    }
}
