<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Condition;

/**
 * @experimental
 */
class MeterConfigurator
{
    /**
     * @param list<Condition> $conditions
     */
    public function __construct(private readonly array $conditions = [])
    {
    }

    public function getConfig(InstrumentationScopeInterface $scope): MeterConfig
    {
        foreach ($this->conditions as $condition) {
            if ($condition->match($scope)) {
                return new MeterConfig($condition->state());
            }
        }

        return MeterConfig::default();
    }
}
