<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Extension\Propagator\B3\B3Propagator;

#[PackageDependency('open-telemetry/extension-propagator-b3', '^1')]
class B3PropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return B3Propagator::getB3SingleHeaderInstance();
    }

    public function type(): string
    {
        return 'b3';
    }

    public function priority(): int
    {
        return 0;
    }
}
