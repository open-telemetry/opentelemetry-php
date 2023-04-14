<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

/**
 * @internal
 */
final class SynchronousMetricCollector implements MetricCollectorInterface
{
    private MetricAggregator $aggregator;

    public function __construct(MetricAggregator $aggregator)
    {
        $this->aggregator = $aggregator;
    }

    public function collect(int $timestamp): Metric
    {
        return $this->aggregator->collect($timestamp);
    }
}
