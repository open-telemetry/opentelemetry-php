<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

;

use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;

class BaggagePropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return BaggagePropagator::getInstance();
    }

    public function type(): string
    {
        return KnownValues::VALUE_BAGGAGE;
    }

    public function priority(): int
    {
        return 0;
    }
}
