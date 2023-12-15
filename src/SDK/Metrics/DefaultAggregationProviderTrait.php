<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

trait DefaultAggregationProviderTrait
{
    public function defaultAggregation($instrumentType, array $advisory = []): ?AggregationInterface
    {
        switch ($instrumentType) {
            case InstrumentType::COUNTER:
            case InstrumentType::ASYNCHRONOUS_COUNTER:
                return new Aggregation\SumAggregation(true);
            case InstrumentType::UP_DOWN_COUNTER:
            case InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER:
                return new Aggregation\SumAggregation();
            case InstrumentType::HISTOGRAM:
                return new Aggregation\ExplicitBucketHistogramAggregation($advisory['ExplicitBucketBoundaries'] ?? [0, 5, 10, 25, 50, 75, 100, 250, 500, 1000]);
            case InstrumentType::ASYNCHRONOUS_GAUGE:
                return new Aggregation\LastValueAggregation();
        }

        // @codeCoverageIgnoreStart
        return null;
        // @codeCoverageIgnoreEnd
    }
}
