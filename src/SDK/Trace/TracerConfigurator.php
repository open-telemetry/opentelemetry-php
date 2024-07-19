<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Trace;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Condition;

/**
 * @internal
 */
class TracerConfigurator
{
    /**
     * @param list<Condition> $conditions
     */
    public function __construct(private readonly array $conditions = [])
    {
    }

    public function getConfig(InstrumentationScopeInterface $scope): TracerConfig
    {
        foreach ($this->conditions as $condition) {
            if ($condition->match($scope)) {
                return new TracerConfig($condition->state());
            }
        }

        return TracerConfig::default();
    }

    public static function builder(): TracerConfiguratorBuilder
    {
        return new TracerConfiguratorBuilder();
    }
}
