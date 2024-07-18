<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Logs;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

/**
 * @experimental
 */
class LoggerConfigurator
{
    public function __invoke(InstrumentationScopeInterface $scope): LoggerConfig
    {
        return new LoggerConfig();
    }
}
