<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\Metrics\Observer;
use OpenTelemetry\SDK\AttributesFactory;
use OpenTelemetry\SDK\Metrics\Aggregation;
use OpenTelemetry\SDK\Metrics\AttributeProcessor;
use OpenTelemetry\SDK\Metrics\Data\Data;
use OpenTelemetry\SDK\Metrics\Data\Temporality;
use OpenTelemetry\SDK\Metrics\Exemplar\ExemplarReservoir;
use function array_search;
use function count;

final class AsynchronousMetricStream implements MetricStream {

    private MetricAggregator $metricAggregator;
    private AttributesFactory $attributes;
    private Aggregation $aggregation;
    private ?ExemplarReservoir $exemplarReservoir;
    private $instrument;

    private int $startTimestamp;

    /** @var array<int, LastRead|null> */
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
        $this->exemplarReservoir = $exemplarReservoir;
        $this->instrument = $instrument;
        $this->startTimestamp = $startTimestamp;
    }

    public function register(Temporality $temporality): int {
        if ($temporality === Temporality::Cumulative) {
            return -1;
        }

        if (($reader = array_search(null, $this->lastReads, true)) === false) {
            $reader = count($this->lastReads);
        }

        $this->lastReads[$reader] = new LastRead(new Metric(), $this->startTimestamp);

        return $reader;
    }

    public function unregister(int $reader): void {
        if (!isset($this->lastReads[$reader])) {
            return;
        }

        $this->lastReads[$reader] = null;
    }

    public function collect(int $reader, int $timestamp): Data {
        ($this->instrument)(new AsynchronousMetricStreamObserver($this->metricAggregator, $this->attributes, $timestamp));

        $metric = $this->metricAggregator->collect();
        $exemplars = $this->exemplarReservoir?->collect($metric->attributes) ?? [];

        if (!$lastRead = $this->lastReads[$reader] ?? null) {
            $temporality = Temporality::Cumulative;
            $startTimestamp = $this->startTimestamp;
        } else {
            $temporality = Temporality::Delta;
            $startTimestamp = $lastRead->timestamp;

            $this->diffInto($lastRead->metric, $metric);
            [$lastRead->metric, $metric] = [$metric, $lastRead->metric];
            $lastRead->timestamp = $timestamp;
        }

        $data = $this->aggregation->toData(
            $metric->attributes,
            $metric->summaries,
            $exemplars,
            $startTimestamp,
            $timestamp,
            $temporality,
        );

        return $data;
    }

    private function diffInto(Metric $into, Metric $metric): void {
        $summaries = $metric->summaries;
        foreach ($metric->summaries as $k => $summary) {
            if (!isset($into->summaries[$k])) {
                continue;
            }

            $summaries[$k] = $this->aggregation->diff($into->summaries[$k], $summary);
        }

        $into->attributes = $metric->attributes;
        $into->summaries = $summaries;
    }
}
