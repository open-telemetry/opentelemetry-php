<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Extension\Propagator\CloudTrace\CloudTraceOneWayPropagatorFactory;
use OpenTelemetry\Extension\Propagator\CloudTrace\CloudTracePropagatorFactory;

if (class_exists(ServiceLoader::class)) {
    ServiceLoader::register(TextMapPropagatorFactoryInterface::class, CloudTracePropagatorFactory::class);
    ServiceLoader::register(TextMapPropagatorFactoryInterface::class, CloudTraceOneWayPropagatorFactory::class);
}
