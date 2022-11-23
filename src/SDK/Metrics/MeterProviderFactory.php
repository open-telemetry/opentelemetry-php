<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use InvalidArgumentException;
use OpenTelemetry\SDK\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\FactoryRegistry;
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

    public function create(): MeterProviderInterface
    {
        if (Sdk::isDisabled()) {
            return new NoopMeterProvider();
        }
        $exporters = Configuration::getList(Variables::OTEL_METRICS_EXPORTER);
        //TODO "The SDK MAY accept a comma-separated list to enable setting multiple exporters"
        if (count($exporters) !== 1) {
            throw new InvalidArgumentException(sprintf('Configuration %s requires exactly 1 exporter', Variables::OTEL_METRICS_EXPORTER));
        }
        $exporterName = $exporters[0];

        try {
            $factory = FactoryRegistry::metricExporterFactory($exporterName);
            $exporter = $factory->create();
        } catch (\Throwable $t) {
            self::logWarning(sprintf('Unable to create %s meter provider: %s', $exporterName, $t->getMessage()));
            $exporter = new NoopMetricExporter();
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
