<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\B3;

use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

class B3PropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return B3Propagator::getB3SingleHeaderInstance();
    }

    public function type(): string
    {
        return 'b3';
    }

    public function priority(): int
    {
        return 0;
    }
}
