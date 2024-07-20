<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

/**
 * @internal
 */
class Configurator
{
    /**
     * @param list<Condition> $conditions
     */
    public function __construct(private readonly array $conditions = [])
    {
    }

    public function getConfig(InstrumentationScopeInterface $scope): Config
    {
        foreach ($this->conditions as $condition) {
            if ($condition->match($scope)) {
                return new Config($condition->state());
            }
        }

        return Config::default();
    }

    public static function builder(): ConfiguratorBuilder
    {
        return new ConfiguratorBuilder();
    }
}