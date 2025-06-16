<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General\HttpConfigProvider;
use OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General\PeerConfigProvider;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordExporterConsole;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordExporterOtlpFile;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordExporterOtlpGrpc;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordExporterOtlpHttp;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordProcessorBatch;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordProcessorSimple;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\AggregationResolverDefault;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterConsole;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterOtlpFile;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterOtlpGrpc;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterOtlpHttp;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricReaderPeriodic;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorB3;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorB3Multi;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorBaggage;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorCloudTrace;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorComposite;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorJaeger;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorTraceContext;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerAlwaysOff;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerAlwaysOn;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerParentBased;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerTraceIdRatioBased;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterConsole;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterOtlpFile;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterOtlpGrpc;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterOtlpHttp;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterZipkin;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanProcessorBatch;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanProcessorSimple;
use OpenTelemetry\API\Configuration\Config\ComponentProvider;

ServiceLoader::register(ComponentProvider::class, TextMapPropagatorB3::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorB3Multi::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorBaggage::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorComposite::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorCloudTrace::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorJaeger::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorTraceContext::class);

ServiceLoader::register(ComponentProvider::class, SamplerAlwaysOff::class);
ServiceLoader::register(ComponentProvider::class, SamplerAlwaysOn::class);
ServiceLoader::register(ComponentProvider::class, SamplerParentBased::class);
ServiceLoader::register(ComponentProvider::class, SamplerTraceIdRatioBased::class);

ServiceLoader::register(ComponentProvider::class, SpanExporterConsole::class);
ServiceLoader::register(ComponentProvider::class, SpanExporterOtlpFile::class);
ServiceLoader::register(ComponentProvider::class, SpanExporterOtlpGrpc::class);
ServiceLoader::register(ComponentProvider::class, SpanExporterOtlpHttp::class);
ServiceLoader::register(ComponentProvider::class, SpanExporterZipkin::class);
ServiceLoader::register(ComponentProvider::class, SpanProcessorBatch::class);
ServiceLoader::register(ComponentProvider::class, SpanProcessorSimple::class);

ServiceLoader::register(ComponentProvider::class, AggregationResolverDefault::class);
ServiceLoader::register(ComponentProvider::class, MetricExporterConsole::class);
ServiceLoader::register(ComponentProvider::class, MetricExporterOtlpFile::class);
ServiceLoader::register(ComponentProvider::class, MetricExporterOtlpGrpc::class);
ServiceLoader::register(ComponentProvider::class, MetricExporterOtlpHttp::class);
ServiceLoader::register(ComponentProvider::class, MetricReaderPeriodic::class);

ServiceLoader::register(ComponentProvider::class, LogRecordExporterConsole::class);
ServiceLoader::register(ComponentProvider::class, LogRecordExporterOtlpFile::class);
ServiceLoader::register(ComponentProvider::class, LogRecordExporterOtlpGrpc::class);
ServiceLoader::register(ComponentProvider::class, LogRecordExporterOtlpHttp::class);
ServiceLoader::register(ComponentProvider::class, LogRecordProcessorBatch::class);
ServiceLoader::register(ComponentProvider::class, LogRecordProcessorSimple::class);

ServiceLoader::register(ComponentProvider::class, HttpConfigProvider::class);
ServiceLoader::register(ComponentProvider::class, PeerConfigProvider::class);
