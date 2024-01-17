<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use InvalidArgumentException;
use OpenTelemetry\API\Behavior\LogsMessagesTrait;
use OpenTelemetry\SDK\Common\Configuration\Configuration;
use OpenTelemetry\SDK\Common\Configuration\KnownValues;
use OpenTelemetry\SDK\Common\Configuration\Variables;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\AllExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\NoneExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilter\WithSampledTraceExemplarFilter;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarFilterInterface;
use OpenTelemetry\SDK\Metrics\MetricExporter\NoopMetricExporter;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Registry;
use OpenTelemetry\SDK\Resource\ResourceInfo;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Sdk;

class MeterProviderFactory
{
    use LogsMessagesTrait;

    /**
     * @todo https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/sdk_exporters/otlp.md#general
     *       - "The exporter MUST configure the default aggregation on the basis of instrument kind using the
     *         OTEL_EXPORTER_OTLP_METRICS_DEFAULT_HISTOGRAM_AGGREGATION variable as described below if it is implemented."
     */
    public function create(?ResourceInfo $resource = null): MeterProviderInterface
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
            $factory = Registry::metricExporterFactory($exporterName);
            $exporter = $factory->create();
        } catch (\Throwable $t) {
            self::logWarning(sprintf('Unable to create %s meter provider: %s', $exporterName, $t->getMessage()));
            $exporter = new NoopMetricExporter();
        }

        // @todo "The exporter MUST be paired with a periodic exporting MetricReader"
        $reader = new ExportingReader($exporter);
        $resource ??= ResourceInfoFactory::defaultResource();
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
