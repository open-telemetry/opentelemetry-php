<?php

declare(strict_types=1);

namespace OpenTelemetry\API\Baggage\Propagation;

use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;

class BaggagePropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return BaggagePropagator::getInstance();
    }

    public function type(): string
    {
        return 'baggage';
    }

    public function priority(): int
    {
        return 0;
    }
}
