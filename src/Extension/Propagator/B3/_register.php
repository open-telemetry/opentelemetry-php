<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Extension\Propagator\B3\B3MultiPropagatorFactory;
use OpenTelemetry\Extension\Propagator\B3\B3PropagatorFactory;

if (class_exists(ServiceLoader::class)) {
    ServiceLoader::register(TextMapPropagatorFactoryInterface::class, B3PropagatorFactory::class);
    ServiceLoader::register(TextMapPropagatorFactoryInterface::class, B3MultiPropagatorFactory::class);
}
