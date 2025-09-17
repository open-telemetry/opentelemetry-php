<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricExporter;

use ArrayObject;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelector;
use OpenTelemetry\SDK\Metrics\AggregationTemporalitySelectorInterface;
use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;
use OpenTelemetry\SDK\Metrics\PushMetricExporterInterface;

/**
 * @see https://github.com/open-telemetry/opentelemetry-specification/blob/main/specification/metrics/sdk_exporters/in-memory.md
 */
final class InMemoryExporter implements MetricExporterInterface, AggregationTemporalitySelectorInterface, PushMetricExporterInterface
{
    private bool $closed = false;
    private readonly AggregationTemporalitySelectorInterface $selector;

    /**
     * @template-implements ArrayObject<Metric> $storage
     * @param ArrayObject $storage
     * @param AggregationTemporalitySelectorInterface|null $selector
     */
    public function __construct(
        private ArrayObject $storage = new ArrayObject(),
        ?AggregationTemporalitySelectorInterface $selector = null,
    ) {
        $this->selector = $selector ?? AggregationTemporalitySelector::alwaysCumulative();
    }

    public function temporality(MetricMetadataInterface $metric): ?Temporality
    {
        return $this->selector->temporality($metric);
    }

    /**
     * @return Metric[]
     */
    public function collect(bool $reset = false): array
    {
        $metrics = $this->storage->getArrayCopy();
        if ($reset) {
            $this->storage = new ArrayObject();
        }

        return $metrics;
    }

    public function export(iterable $batch): bool
    {
        if ($this->closed) {
            return false;
        }

        foreach ($batch as $metric) {
            $this->storage->append($metric);
        }

        return true;
    }

    public function shutdown(): bool
    {
        if ($this->closed) {
            return false;
        }

        $this->closed = true;

        return true;
    }

    public function forceFlush(): bool
    {
        return true;
    }
}
