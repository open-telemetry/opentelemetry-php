<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\Extension\Propagator\CloudTrace\CloudTraceOneWayPropagatorFactory;
use OpenTelemetry\Extension\Propagator\CloudTrace\CloudTracePropagatorFactory;

ServiceLoader::register(TextMapPropagatorFactoryInterface::class, CloudTracePropagatorFactory::class);
ServiceLoader::register(TextMapPropagatorFactoryInterface::class, CloudTraceOneWayPropagatorFactory::class);
