<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Contrib\Otlp\LogsExporterFactory;
use OpenTelemetry\Contrib\Otlp\MetricExporterFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporterFactory;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;

ServiceLoader::register(SpanExporterFactoryInterface::class, SpanExporterFactory::class);
ServiceLoader::register(MetricExporterFactoryInterface::class, MetricExporterFactory::class);
ServiceLoader::register(LogRecordExporterFactoryInterface::class, LogsExporterFactory::class);
