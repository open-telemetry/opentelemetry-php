<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

class Configurator implements ScopeConfigurator
{
    /**
     * @param list<Condition> $conditions
     */
    public function __construct(private readonly array $conditions = [])
    {
    }

    /**
     * @internal
     */
    public function getConfig(InstrumentationScopeInterface $scope): Config
    {
        foreach ($this->conditions as $condition) {
            if ($condition->matches($scope)) {
                return new Config($condition->state());
            }
        }

        return Config::default();
    }

    public static function builder(): ConfiguratorBuilder
    {
        return new ConfiguratorBuilder();
    }

    public static function default(): ScopeConfigurator
    {
        return new self();
    }
}
