<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK;

use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\NoopResponsePropagator;
use OpenTelemetry\Context\Propagation\NoopTextMapPropagator;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory;
use OpenTelemetry\SDK\Logs\Exporter\InMemoryExporterFactory as InMemoryLogsExporterFactory;
use OpenTelemetry\SDK\Metrics\MetricExporter\ConsoleMetricExporterFactory;
use OpenTelemetry\SDK\Metrics\MetricExporter\InMemoryExporterFactory as InMemoryMetricExporterFactory;
use OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemorySpanExporterFactory;

/**
 * Registers the components that are part of the SDK.
 *
 * @internal
 */
final class SdkRegistrar implements RegistrarInterface
{
    #[\Override]
    public function register(): void
    {
        Registry::registerSpanExporterFactory('console', ConsoleSpanExporterFactory::class);
        Registry::registerSpanExporterFactory('memory', InMemorySpanExporterFactory::class);

        Registry::registerMetricExporterFactory('memory', InMemoryMetricExporterFactory::class);
        Registry::registerMetricExporterFactory('console', ConsoleMetricExporterFactory::class);
        Registry::registerMetricExporterFactory('none', NoopMetricExporterFactory::class);

        Registry::registerLogRecordExporterFactory('console', ConsoleExporterFactory::class);
        Registry::registerLogRecordExporterFactory('memory', InMemoryLogsExporterFactory::class);

        Registry::registerTransportFactory('stream', StreamTransportFactory::class);

        Registry::registerTextMapPropagator(KnownValues::VALUE_BAGGAGE, BaggagePropagator::getInstance());
        Registry::registerTextMapPropagator(KnownValues::VALUE_TRACECONTEXT, TraceContextPropagator::getInstance());
        Registry::registerTextMapPropagator(KnownValues::VALUE_NONE, NoopTextMapPropagator::getInstance());

        Registry::registerResponsePropagator(KnownValues::VALUE_NONE, NoopResponsePropagator::getInstance());
    }
}
