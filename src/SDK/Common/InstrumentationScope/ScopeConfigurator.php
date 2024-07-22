<?php

namespace OpenTelemetry\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

interface ScopeConfigurator
{
    public function getConfig(InstrumentationScopeInterface $scope): Config;
}