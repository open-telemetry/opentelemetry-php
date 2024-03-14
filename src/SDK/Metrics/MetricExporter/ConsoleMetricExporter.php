<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeInterface;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelectorInterface;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\PushMetricExporterInterface;
use OpenTelemetry\SDK\Resource\ResourceInfo;

/**
 * Console metrics exporter.
 * Note that the output is human-readable JSON, not compatible with OTLP.
 */
class ConsoleMetricExporter implements PushMetricExporterInterface, AggregationTemporalitySelectorInterface
{
    public function __construct(private readonly Temporality|string|null $temporality = null)
    {
    }
    /**
     * @inheritDoc
     */
    public function temporality(MetricMetadataInterface $metric): Temporality|string|null
    {
        return $this->temporality ?? $metric->temporality();
    }

    /**
     * @inheritDoc
     */
    public function export(iterable $batch): bool
    {
        $resource = null;
        $scope = null;
        foreach ($batch as $metric) {
            /** @var Metric $metric */
            if (!$resource) {
                $resource = $this->convertResource($metric->resource);
            }
            if (!$scope) {
                $scope = $this->convertInstrumentationScope($metric->instrumentationScope);
                $scope['metrics'] = [];
            }
            $scope['metrics'][] = $this->convertMetric($metric);
        }
        $output = [
            'resource' => $resource,
            'scope' => $scope,
        ];
        echo json_encode($output, JSON_PRETTY_PRINT) . PHP_EOL;

        return true;
    }

    public function shutdown(): bool
    {
        return true;
    }

    public function forceFlush(): bool
    {
        return true;
    }

    private function convertMetric(Metric $metric): array
    {
        return [
            'name' => $metric->name,
            'description' => $metric->description,
            'unit' => $metric->unit,
            'data' => $metric->data,
        ];
    }

    private function convertResource(ResourceInfo $resource): array
    {
        return [
            'attributes' => $resource->getAttributes()->toArray(),
            'dropped_attributes_count' => $resource->getAttributes()->getDroppedAttributesCount(),
        ];
    }

    private function convertInstrumentationScope(InstrumentationScopeInterface $scope): array
    {
        return [
            'name' => $scope->getName(),
            'version' => $scope->getVersion(),
            'attributes' => $scope->getAttributes()->toArray(),
            'dropped_attributes_count' => $scope->getAttributes()->getDroppedAttributesCount(),
            'schema_url' => $scope->getSchemaUrl(),
        ];
    }
}
