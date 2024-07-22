<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;

class Attribute implements Predicate
{
    public function __construct(
        private readonly string $key,
        private readonly mixed $value,
    ) {
    }

    public function matches(InstrumentationScopeInterface $scope): bool
    {
        if (!$scope->getAttributes()->has($this->key)) {
            return false;
        }
        $attribute = $scope->getAttributes()->get($this->key);

        return $attribute === $this->value;
    }
}
