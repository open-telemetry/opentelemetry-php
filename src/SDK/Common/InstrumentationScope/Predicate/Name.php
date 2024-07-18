<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Common\InstrumentationScope\Predicate;

class Name implements Predicate
{
    public function __construct(private readonly string $regex)
    {
    }

    /**
     * @psalm-suppress ArgumentTypeCoercion
     */
    public function match(InstrumentationScopeInterface $scope): bool
    {
        $result = preg_match($this->regex, $scope->getName());

        return $result > 0;
    }
}
