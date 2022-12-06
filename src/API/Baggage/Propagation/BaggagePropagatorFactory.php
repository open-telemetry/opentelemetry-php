<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage\Propagation;

use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

class BaggagePropagatorFactory implements \OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return BaggagePropagator::getInstance();
    }
}
