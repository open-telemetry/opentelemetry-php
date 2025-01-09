<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\CloudTrace;

use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

class CloudTracePropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return CloudTracePropagator::getInstance();
    }

    public function type(): string
    {
        return 'cloudtrace';
    }

    public function priority(): int
    {
        return 0;
    }
}
