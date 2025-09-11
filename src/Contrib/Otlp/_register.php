<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Contrib\Otlp\LogsExporterFactory;
use OpenTelemetry\Contrib\Otlp\MemoryTransportFactory;
use OpenTelemetry\Contrib\Otlp\MetricExporterFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporterFactory;
use OpenTelemetry\Contrib\Otlp\StdoutTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;

ServiceLoader::register(SpanExporterFactoryInterface::class, SpanExporterFactory::class);
ServiceLoader::register(MetricExporterFactoryInterface::class, MetricExporterFactory::class);
ServiceLoader::register(LogRecordExporterFactoryInterface::class, LogsExporterFactory::class);

ServiceLoader::register(TransportFactoryInterface::class, StdoutTransportFactory::class);
ServiceLoader::register(TransportFactoryInterface::class, MemoryTransportFactory::class);
