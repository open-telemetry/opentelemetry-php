<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;

/**
 * Predicate which matches on the existence of an InstrumentationScope attribute.
 */
class AttributeExists implements Predicate
{
    public function __construct(
        private readonly string $key,
    ) {
    }

    public function matches(InstrumentationScopeInterface $scope): bool
    {
        return $scope->getAttributes()->has($this->key);
    }
}
