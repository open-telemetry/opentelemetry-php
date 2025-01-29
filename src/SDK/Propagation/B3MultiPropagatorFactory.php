<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Extension\Propagator\B3\B3Propagator;

#[PackageDependency('open-telemetry/extension-propagator-b3', '^1')]
class B3MultiPropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return B3Propagator::getB3MultiHeaderInstance();
    }

    public function type(): string
    {
        return 'b3multi';
    }

    public function priority(): int
    {
        return 0;
    }
}
