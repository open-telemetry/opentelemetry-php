<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Propagation;

use Nevay\SPI\ServiceProviderDependency\PackageDependency;
use OpenTelemetry\Context\Propagation\TextMapPropagatorInterface;
use OpenTelemetry\Extension\Propagator\Jaeger\JaegerPropagator;

#[PackageDependency('open-telemetry/extension-propagator-jaeger', '^0')]
class JaegerPropagatorFactory implements TextMapPropagatorFactoryInterface
{
    public function create(): TextMapPropagatorInterface
    {
        return JaegerPropagator::getInstance();
    }

    public function type(): string
    {
        return 'jaeger';
    }

    public function priority(): int
    {
        return 0;
    }
}
