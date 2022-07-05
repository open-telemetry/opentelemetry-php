<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\API\Metrics\Observer;
use OpenTelemetry\SDK\AttributesFactory;
use OpenTelemetry\SDK\Metrics\Aggregation;
use OpenTelemetry\SDK\Metrics\AttributeProcessor;
use OpenTelemetry\SDK\Metrics\Data\Data;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoir;
use function array_search;
use function assert;
use function count;

final class AsynchronousMetricStream implements MetricStream {

    private MetricAggregator $metricAggregator;
    private AttributesFactory $attributes;
    private Aggregation $aggregation;
    private $instrument;

    private int $startTimestamp;
    private Metric $metric;
    private bool $locked = false;

    /** @var array<int, Metric|null> */
    private array $lastReads = [];

    /**
     * @param callable(Observer): void $instrument
     */
    public function __construct(
        AttributesFactory $attributes,
        ?AttributeProcessor $attributeProcessor,
        Aggregation $aggregation,
        ?ExemplarReservoir $exemplarReservoir,
        callable $instrument,
        int $startTimestamp,
    ) {
        $this->metricAggregator = new MetricAggregator(
            $attributeProcessor,
            $aggregation,
            $exemplarReservoir,
        );
        $this->attributes = $attributes;
        $this->aggregation = $aggregation;
        $this->instrument = $instrument;
        $this->startTimestamp = $startTimestamp;
        $this->metric = new Metric([], [], $startTimestamp, -1);
    }

    public function temporality(): Temporality {
        return Temporality::Cumulative;
    }

    public function collectionTimestamp(): int {
        return $this->metric->timestamp;
    }

    public function register(Temporality $temporality): int {
        if ($temporality === Temporality::Cumulative) {
            return -1;
        }

        if (($reader = array_search(null, $this->lastReads, true)) === false) {
            $reader = count($this->lastReads);
        }

        $this->lastReads[$reader] = $this->metric;

        return $reader;
    }

    public function unregister(int $reader): void {
        if (!isset($this->lastReads[$reader])) {
            return;
        }

        $this->lastReads[$reader] = null;
    }

    public function collect(int $reader, ?int $timestamp): Data {
        if ($timestamp !== null && !$this->locked) {
            $this->locked = true;
            try {
                ($this->instrument)(new AsynchronousMetricStreamObserver($this->metricAggregator, $this->attributes, $timestamp));

                $this->metric = $this->metricAggregator->collect($timestamp);
            } finally {
                $this->locked = false;
            }
        }

        $metric = $this->metric;

        if (!$lastRead = $this->lastReads[$reader] ?? null) {
            $temporality = Temporality::Cumulative;
            $startTimestamp = $this->startTimestamp;
        } else {
            $temporality = Temporality::Delta;
            $startTimestamp = $lastRead->timestamp;

            $this->lastReads[$reader] = $metric;
            $metric = $this->diff($lastRead, $metric);
        }

        $data = $this->aggregation->toData(
            $metric->attributes,
            $metric->summaries,
            $this->metricAggregator->exemplars($metric),
            $startTimestamp,
            $metric->timestamp,
            $temporality,
        );

        return $data;
    }

    private function diff(Metric $lastRead, Metric $metric): Metric {
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
