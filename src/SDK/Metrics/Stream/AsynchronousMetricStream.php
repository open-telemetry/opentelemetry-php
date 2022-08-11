<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use function array_search;
use function assert;
use function count;
use OpenTelemetry\API\Metrics\ObserverInterface;
use OpenTelemetry\SDK\Common\Attribute\AttributesFactoryInterface;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\AttributeProcessorInterface;
use OpenTelemetry\SDK\Metrics\Data\DataInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoirInterface;

/**
 * @internal
 */
final class AsynchronousMetricStream implements MetricStreamInterface
{
    private MetricAggregator $metricAggregator;
    private AttributesFactoryInterface $attributesFactory;
    private AggregationInterface $aggregation;
    private $instrument;

    private int $startTimestamp;
    private Metric $metric;
    private bool $locked = false;

    /** @var array<int, Metric|null> */
    private array $lastReads = [];

    /**
     * @param callable(ObserverInterface): void $instrument
     */
    public function __construct(
        AttributesFactoryInterface $attributesFactory,
        ?AttributeProcessorInterface $attributeProcessor,
        AggregationInterface $aggregation,
        ?ExemplarReservoirInterface $exemplarReservoir,
        callable $instrument,
        int $startTimestamp
    ) {
        $this->metricAggregator = new MetricAggregator(
            $attributeProcessor,
            $aggregation,
            $exemplarReservoir,
        );
        $this->attributesFactory = $attributesFactory;
        $this->aggregation = $aggregation;
        $this->instrument = $instrument;
        $this->startTimestamp = $startTimestamp;
        $this->metric = new Metric([], [], $startTimestamp, -1);
    }

    public function temporality()
    {
        return Temporality::CUMULATIVE;
    }

    public function collectionTimestamp(): int
    {
        return $this->metric->timestamp;
    }

    public function register($temporality): int
    {
        if ($temporality === Temporality::CUMULATIVE) {
            return -1;
        }

        if (($reader = array_search(null, $this->lastReads, true)) === false) {
            $reader = count($this->lastReads);
        }

        $this->lastReads[$reader] = $this->metric;

        return $reader;
    }

    public function unregister(int $reader): void
    {
        if (!isset($this->lastReads[$reader])) {
            return;
        }

        $this->lastReads[$reader] = null;
    }

    public function collect(int $reader, ?int $timestamp): DataInterface
    {
        if ($timestamp !== null && !$this->locked) {
            $this->locked = true;

            try {
                ($this->instrument)(new AsynchronousMetricStreamObserver($this->metricAggregator, $this->attributesFactory, $timestamp));

                $this->metric = $this->metricAggregator->collect($timestamp);
            } finally {
                $this->locked = false;
            }
        }

        $metric = $this->metric;

        if (($lastRead = $this->lastReads[$reader] ?? null) === null) {
            $temporality = Temporality::CUMULATIVE;
            $startTimestamp = $this->startTimestamp;
        } else {
            $temporality = Temporality::DELTA;
            $startTimestamp = $lastRead->timestamp;

            $this->lastReads[$reader] = $metric;
            $metric = $this->diff($lastRead, $metric);
        }

        return $this->aggregation->toData(
            $metric->attributes,
            $metric->summaries,
            $this->metricAggregator->exemplars($metric),
            $startTimestamp,
            $metric->timestamp,
            $temporality,
        );
    }

    private function diff(Metric $lastRead, Metric $metric): Metric
    {
        assert($lastRead->revision <= $metric->revision);

        $diff = clone $metric;
        foreach ($metric->summaries as $k => $summary) {
            if (!isset($lastRead->summaries[$k])) {
                continue;
            }

            $diff->summaries[$k] = $this->aggregation->diff($lastRead->summaries[$k], $summary);
        }

        return $diff;
    }
}
