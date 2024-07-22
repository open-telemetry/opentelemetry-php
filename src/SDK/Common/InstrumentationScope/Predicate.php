<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

interface Predicate
{
    public function matches(InstrumentationScopeInterface $scope): bool;
}
