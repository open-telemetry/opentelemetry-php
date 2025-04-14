<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Extension\Propagator\Jaeger\JaegerBaggagePropagator;

#[PackageDependency('open-telemetry/extension-propagator-jaeger', '^0')]
class JaegerBaggagePropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return JaegerBaggagePropagator::getInstance();
    }

    public function type(): string
    {
        return 'jaeger-baggage';
    }

    public function priority(): int
    {
        return 0;
    }
}
