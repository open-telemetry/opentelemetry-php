<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Environment\EnvironmentVariables;
use OpenTelemetry\SDK\Common\Environment\KnownValues;
use OpenTelemetry\SDK\Common\Environment\Variables;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Sdk;

class MeterProviderFactory
{
    use LogsMessagesTrait;

    private const KNOWN_EXPORTER_FACTORIES = [
        KnownValues::VALUE_OTLP => '\OpenTelemetry\Contrib\Otlp\MetricExporterFactory',
    ];

    public function create(): MeterProviderInterface
    {
        if (Sdk::isDisabled()) {
            return new NoopMeterProvider();
        }
        /**
         * @todo:
         * - OTEL_METRICS_EXEMPLAR_FILTER
         * - OTEL_METRIC_EXPORT_INTERVAL
         * - OTEL_METRIC_EXPORT_TIMEOUT
         * - OTEL_EXPORTER_OTLP_METRICS_HEADERS
         */

        $exporterName = EnvironmentVariables::getString(Variables::OTEL_METRICS_EXPORTER, KnownValues::VALUE_NONE);
        if ($exporterName === KnownValues::VALUE_NONE) {
            $exporter = new NoopMetricExporter();
        } elseif (!array_key_exists($exporterName, self::KNOWN_EXPORTER_FACTORIES)) {
            self::logError('Factory cannot create exporter: ' . $exporterName);
            $exporter = new NoopMetricExporter();
        } else {
            $factoryClass = self::KNOWN_EXPORTER_FACTORIES[$exporterName];
            $factory = new $factoryClass();
            $exporter = $factory->create();
        }
        $reader = new ExportingReader($exporter, ClockFactory::getDefault());
        $resource = ResourceInfoFactory::defaultResource(); //@todo ??

        return MeterProvider::builder()
            ->setResource($resource)
            ->addReader($reader)
            ->build();
    }
}
