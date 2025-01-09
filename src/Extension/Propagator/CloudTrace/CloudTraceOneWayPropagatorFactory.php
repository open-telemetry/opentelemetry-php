<?php

declare(strict_types=1);

namespace OpenTelemetry\Extension\Propagator\CloudTrace;

use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

class CloudTraceOneWayPropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return CloudTracePropagator::getOneWayInstance();
    }

    public function type(): string
    {
        return 'cloudtrace-oneway';
    }

    public function priority(): int
    {
        return 0;
    }
}
