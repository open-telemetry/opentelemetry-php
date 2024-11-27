<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporterFactory;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporterFactory;
use OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporterFactory;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;

ServiceLoader::register(MetricExporterFactoryInterface::class, InMemoryExporterFactory::class);
ServiceLoader::register(MetricExporterFactoryInterface::class, ConsoleMetricExporterFactory::class);
ServiceLoader::register(MetricExporterFactoryInterface::class, NoopMetricExporterFactory::class);
