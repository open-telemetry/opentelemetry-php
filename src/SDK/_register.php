<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;

use OpenTelemetry\Context\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\Exporter as LogsExporter;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporter;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Propagation\BaggagePropagatorFactory;
use OpenTelemetry\SDK\Propagation\TraceContextPropagatorFactory;
use OpenTelemetry\SDK\Trace\SpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;

ServiceLoader::register(TextMapPropagatorFactoryInterface::class, BaggagePropagatorFactory::class);
ServiceLoader::register(TextMapPropagatorFactoryInterface::class, TraceContextPropagatorFactory::class);

ServiceLoader::register(LogRecordExporterFactoryInterface::class, LogsExporter\ConsoleExporterFactory::class);
ServiceLoader::register(LogRecordExporterFactoryInterface::class, LogsExporter\InMemoryExporterFactory::class);

ServiceLoader::register(MetricExporterFactoryInterface::class, MetricExporter\InMemoryExporterFactory::class);
ServiceLoader::register(MetricExporterFactoryInterface::class, MetricExporter\ConsoleMetricExporterFactory::class);
ServiceLoader::register(MetricExporterFactoryInterface::class, MetricExporter\NoopMetricExporterFactory::class);

ServiceLoader::register(SpanExporterFactoryInterface::class, SpanExporter\ConsoleSpanExporterFactory::class);
ServiceLoader::register(SpanExporterFactoryInterface::class, SpanExporter\InMemorySpanExporterFactory::class);

ServiceLoader::register(TransportFactoryInterface::class, StreamTransportFactory::class);
ServiceLoader::register(TransportFactoryInterface::class, PsrTransportFactory::class);
