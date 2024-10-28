<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Baggage\Propagation\BaggagePropagatorFactory;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagatorFactory;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagatorFactory;
use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;

ServiceLoader::register(TextMapPropagatorFactoryInterface::class, BaggagePropagatorFactory::class);
ServiceLoader::register(TextMapPropagatorFactoryInterface::class, TraceContextPropagatorFactory::class);
ServiceLoader::register(TextMapPropagatorFactoryInterface::class, NoopTextMapPropagatorFactory::class);
