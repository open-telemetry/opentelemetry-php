<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;
use OpenTelemetry\Example\ExampleConfigProvider;
use OpenTelemetry\Tests\Integration\Config\ComponentProvider\Metrics\AggregationResolverExplicitBucketHistogram;
use OpenTelemetry\Tests\Integration\Config\ComponentProvider\Metrics\MetricExporterPrometheus;
use OpenTelemetry\Tests\Integration\Config\ComponentProvider\Metrics\MetricReaderPull;
use OpenTelemetry\Tests\Integration\Config\ComponentProvider\Propagator\TextMapPropagatorOtTrace;
use OpenTelemetry\Tests\Integration\Config\ComponentProvider\Propagator\TextMapPropagatorXray;

ServiceLoader::register(ComponentProvider::class, ExampleConfigProvider::class);
ServiceLoader::register(ComponentProvider::class, AggregationResolverExplicitBucketHistogram::class);
ServiceLoader::register(ComponentProvider::class, MetricExporterPrometheus::class);
ServiceLoader::register(ComponentProvider::class, MetricReaderPull::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorXray::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorOtTrace::class);
