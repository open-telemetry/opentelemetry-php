<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Common\Export\TransportFactoryInterface;
use OpenTelemetry\SDK\Logs\Exporter as LogsExporter;
use OpenTelemetry\SDK\Logs\LogRecordExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporter;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Propagation\B3MultiPropagatorFactory;
use OpenTelemetry\SDK\Propagation\B3PropagatorFactory;
use OpenTelemetry\SDK\Propagation\BaggagePropagatorFactory;
use OpenTelemetry\SDK\Propagation\CloudTraceOneWayPropagatorFactory;
use OpenTelemetry\SDK\Propagation\CloudTracePropagatorFactory;
use OpenTelemetry\SDK\Propagation\JaegerBaggagePropagatorFactory;
use OpenTelemetry\SDK\Propagation\JaegerPropagatorFactory;
use OpenTelemetry\SDK\Propagation\TextMapPropagatorFactoryInterface;
use OpenTelemetry\SDK\Propagation\TraceContextPropagatorFactory;
use OpenTelemetry\SDK\Trace\SpanExporter;
use OpenTelemetry\SDK\Trace\SpanExporter\SpanExporterFactoryInterface;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessorFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\HttpMetricsSpanProcessorFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessorFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SpanProcessorFactoryInterface;

ServiceLoader::register(TextMapPropagatorFactoryInterface::class, BaggagePropagatorFactory::class);
ServiceLoader::register(TextMapPropagatorFactoryInterface::class, TraceContextPropagatorFactory::class);
ServiceLoader::register(TextMapPropagatorFactoryInterface::class, B3PropagatorFactory::class);
ServiceLoader::register(TextMapPropagatorFactoryInterface::class, B3MultiPropagatorFactory::class);
ServiceLoader::register(TextMapPropagatorFactoryInterface::class, CloudTracePropagatorFactory::class);
ServiceLoader::register(TextMapPropagatorFactoryInterface::class, CloudTraceOneWayPropagatorFactory::class);
ServiceLoader::register(TextMapPropagatorFactoryInterface::class, JaegerPropagatorFactory::class);
ServiceLoader::register(TextMapPropagatorFactoryInterface::class, JaegerBaggagePropagatorFactory::class);

ServiceLoader::register(LogRecordExporterFactoryInterface::class, LogsExporter\ConsoleExporterFactory::class);
ServiceLoader::register(LogRecordExporterFactoryInterface::class, LogsExporter\InMemoryExporterFactory::class);

ServiceLoader::register(MetricExporterFactoryInterface::class, MetricExporter\InMemoryExporterFactory::class);
ServiceLoader::register(MetricExporterFactoryInterface::class, MetricExporter\ConsoleMetricExporterFactory::class);
ServiceLoader::register(MetricExporterFactoryInterface::class, MetricExporter\NoopMetricExporterFactory::class);

ServiceLoader::register(SpanExporterFactoryInterface::class, SpanExporter\ConsoleSpanExporterFactory::class);
ServiceLoader::register(SpanExporterFactoryInterface::class, SpanExporter\InMemorySpanExporterFactory::class);

ServiceLoader::register(TransportFactoryInterface::class, StreamTransportFactory::class);
ServiceLoader::register(TransportFactoryInterface::class, PsrTransportFactory::class);

ServiceLoader::register(SpanProcessorFactoryInterface::class, BatchSpanProcessorFactory::class);
ServiceLoader::register(SpanProcessorFactoryInterface::class, SimpleSpanProcessorFactory::class);
ServiceLoader::register(SpanProcessorFactoryInterface::class, HttpMetricsSpanProcessorFactory::class);
