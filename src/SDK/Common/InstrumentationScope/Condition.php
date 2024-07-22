<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;

class Condition
{
    public function __construct(
        private readonly Predicate $predicate,
        private readonly State $state,
    ) {
    }

    public function match(InstrumentationScopeInterface $scope): ?bool
    {
        return $this->predicate->matches($scope);
    }

    public function state(): State
    {
        return $this->state;
    }
}
