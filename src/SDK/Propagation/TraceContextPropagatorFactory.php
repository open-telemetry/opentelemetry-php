<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

;

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;

class TraceContextPropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return TraceContextPropagator::getInstance();
    }

    public function type(): string
    {
        return KnownValues::VALUE_TRACECONTEXT;
    }

    public function priority(): int
    {
        return 0;
    }
}
