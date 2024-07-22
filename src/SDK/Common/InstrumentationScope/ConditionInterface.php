<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

interface ConditionInterface
{
    public function matches(InstrumentationScopeInterface $scope): bool;
    public function state(): State;
}
