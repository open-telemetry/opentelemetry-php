<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Extension\Propagator\CloudTrace\CloudTracePropagator;

#[PackageDependency('open-telemetry/extension-propagator-cloudtrace', '^0')]
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
