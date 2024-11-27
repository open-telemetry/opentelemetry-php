<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Extension\Propagator\Jaeger\JaegerBaggagePropagatorFactory;
use OpenTelemetry\Extension\Propagator\Jaeger\JaegerPropagatorFactory;

if (class_exists(ServiceLoader::class)) {
    ServiceLoader::register(TextMapPropagatorFactoryInterface::class, JaegerPropagatorFactory::class);
    ServiceLoader::register(TextMapPropagatorFactoryInterface::class, JaegerBaggagePropagatorFactory::class);
}
