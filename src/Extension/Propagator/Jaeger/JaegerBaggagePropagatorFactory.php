<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\Jaeger;

use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

class JaegerBaggagePropagatorFactory implements TextMapPropagatorFactoryInterface
{

    public function create(): TextMapPropagatorInterface
    {
        return JaegerBaggagePropagator::getInstance();
    }

    public function type(): string
    {
        return 'jaeger-baggage';
    }

    public function priority(): int
    {
        return 0;
    }
}
