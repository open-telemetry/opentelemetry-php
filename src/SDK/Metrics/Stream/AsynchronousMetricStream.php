<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use function array_search;
use function count;
use OpenTelemetry\SDK\Metrics\AggregationInterface;
use OpenTelemetry\SDK\Metrics\Data\DataInterface;
use OpenTelemetry\SDK\Metrics\Data\Exemplar;
use OpenTelemetry\SDK\Metrics\Data\Temporality;

/**
 * @internal
 */
final class AsynchronousMetricStream implements MetricStreamInterface
{
    private Metric $metric;

    /** @var array<int, Metric|null> */
    private array $lastReads = [];

    public function __construct(
        private readonly AggregationInterface $aggregation,
        private readonly int $startTimestamp,
    ) {
        $this->metric = new Metric([], [], $startTimestamp);
    }

    #[\Override]
    public function temporality(): Temporality|string
    {
        return Temporality::CUMULATIVE;
    }

    #[\Override]
    public function timestamp(): int
    {
        return $this->metric->timestamp;
    }

    #[\Override]
    public function push(Metric $metric): void
    {
        $this->metric = $metric;
    }

    #[\Override]
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

    #[\Override]
    public function unregister(int $reader): void
    {
        if (!isset($this->lastReads[$reader])) {
            return;
        }

        $this->lastReads[$reader] = null;
    }

    #[\Override]
    public function collect(int $reader): DataInterface
    {
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
            Exemplar::groupByIndex($metric->exemplars),
            $startTimestamp,
            $metric->timestamp,
            $temporality,
        );
    }

    private function diff(Metric $lastRead, Metric $metric): Metric
    {
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
