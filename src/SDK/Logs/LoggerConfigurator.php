<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Condition;

/**
 * @experimental
 */
class LoggerConfigurator
{
    /**
     * @param list<Condition> $conditions
     */
    public function __construct(private readonly array $conditions = [])
    {
    }

    public function getConfig(InstrumentationScopeInterface $scope): LoggerConfig
    {
        foreach ($this->conditions as $condition) {
            if ($condition->match($scope)) {
                return new LoggerConfig($condition->state());
            }
        }

        return LoggerConfig::default();
    }
}
