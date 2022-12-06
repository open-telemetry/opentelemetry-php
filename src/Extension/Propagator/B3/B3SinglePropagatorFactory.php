<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\B3;

use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

class B3SinglePropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return B3Propagator::getB3SingleHeaderInstance();
    }
}
