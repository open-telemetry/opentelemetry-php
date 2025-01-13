<?php

declare(strict_types=1);

use Nevay\SPI\ServiceLoader;
use OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General\HttpConfigProvider;
use OpenTelemetry\Config\SDK\ComponentProvider\Instrumentation\General\PeerConfigProvider;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordExporterConsole;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordExporterOtlp;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordProcessorBatch;
use OpenTelemetry\Config\SDK\ComponentProvider\Logs\LogRecordProcessorSimple;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\AggregationResolverDefault;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterConsole;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricExporterOtlp;
use OpenTelemetry\Config\SDK\ComponentProvider\Metrics\MetricReaderPeriodic;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorB3;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorB3Multi;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorBaggage;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorComposite;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorJaeger;
use OpenTelemetry\Config\SDK\ComponentProvider\Propagator\TextMapPropagatorTraceContext;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerAlwaysOff;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerAlwaysOn;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerParentBased;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SamplerTraceIdRatioBased;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterConsole;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterOtlp;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanExporterZipkin;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanProcessorBatch;
use OpenTelemetry\Config\SDK\ComponentProvider\Trace\SpanProcessorSimple;
use OpenTelemetry\Config\SDK\Configuration\ComponentProvider;

ServiceLoader::register(ComponentProvider::class, TextMapPropagatorB3::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorB3Multi::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorBaggage::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorComposite::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorJaeger::class);
ServiceLoader::register(ComponentProvider::class, TextMapPropagatorTraceContext::class);

ServiceLoader::register(ComponentProvider::class, SamplerAlwaysOff::class);
ServiceLoader::register(ComponentProvider::class, SamplerAlwaysOn::class);
ServiceLoader::register(ComponentProvider::class, SamplerParentBased::class);
ServiceLoader::register(ComponentProvider::class, SamplerTraceIdRatioBased::class);

ServiceLoader::register(ComponentProvider::class, SpanExporterConsole::class);
ServiceLoader::register(ComponentProvider::class, SpanExporterOtlp::class);
ServiceLoader::register(ComponentProvider::class, SpanExporterZipkin::class);
ServiceLoader::register(ComponentProvider::class, SpanProcessorBatch::class);
ServiceLoader::register(ComponentProvider::class, SpanProcessorSimple::class);

ServiceLoader::register(ComponentProvider::class, AggregationResolverDefault::class);
ServiceLoader::register(ComponentProvider::class, MetricExporterConsole::class);
ServiceLoader::register(ComponentProvider::class, MetricExporterOtlp::class);
ServiceLoader::register(ComponentProvider::class, MetricReaderPeriodic::class);

ServiceLoader::register(ComponentProvider::class, LogRecordExporterConsole::class);
ServiceLoader::register(ComponentProvider::class, LogRecordExporterOtlp::class);
ServiceLoader::register(ComponentProvider::class, LogRecordProcessorBatch::class);
ServiceLoader::register(ComponentProvider::class, LogRecordProcessorSimple::class);

ServiceLoader::register(ComponentProvider::class, HttpConfigProvider::class);
ServiceLoader::register(ComponentProvider::class, PeerConfigProvider::class);
