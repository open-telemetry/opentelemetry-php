<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

interface ScopeConfigurator
{
    public function getConfig(InstrumentationScopeInterface $scope): Config;
    public static function default(): ScopeConfigurator;
}
