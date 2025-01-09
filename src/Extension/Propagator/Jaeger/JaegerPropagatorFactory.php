<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\Jaeger;

use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

class JaegerPropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return JaegerPropagator::getInstance();
    }

    public function type(): string
    {
        return 'jaeger';
    }

    public function priority(): int
    {
        return 0;
    }
}
