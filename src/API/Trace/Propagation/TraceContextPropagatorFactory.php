<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Trace\Propagation;

use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

class TraceContextPropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return TraceContextPropagator::getInstance();
    }

    public function type(): string
    {
        return 'tracecontext';
    }

    public function priority(): int
    {
        return 0;
    }
}
