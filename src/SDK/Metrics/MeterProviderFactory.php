<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
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
        $exporterName = Configuration::getString(Variables::OTEL_METRICS_EXPORTER, KnownValues::VALUE_NONE);
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
        $resource = ResourceInfoFactory::defaultResource();
        $exemplarFilter = $this->createExemplarFilter(Configuration::getEnum(Variables::OTEL_METRICS_EXEMPLAR_FILTER));

        return MeterProvider::builder()
            ->setResource($resource)
            ->addReader($reader)
            ->setExemplarFilter($exemplarFilter)
            ->build();
    }

    private function createExemplarFilter(string $name): ExemplarFilterInterface
    {
        switch ($name) {
            case KnownValues::VALUE_WITH_SAMPLED_TRACE:
                return new WithSampledTraceExemplarFilter();
            case KnownValues::VALUE_ALL:
                return new AllExemplarFilter();
            case KnownValues::VALUE_NONE:
                return new NoneExemplarFilter();
            default:
                self::logWarning('Unknown exemplar filter: ' . $name);

                return new NoneExemplarFilter();
        }
    }
}
