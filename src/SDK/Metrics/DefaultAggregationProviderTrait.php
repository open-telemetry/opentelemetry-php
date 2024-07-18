<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

trait DefaultAggregationProviderTrait
{
    public function defaultAggregation($instrumentType, array $advisory = []): ?AggregationInterface
    {
        return match ($instrumentType) {
            InstrumentType::COUNTER, InstrumentType::ASYNCHRONOUS_COUNTER => new Aggregation\SumAggregation(true),
            InstrumentType::UP_DOWN_COUNTER, InstrumentType::ASYNCHRONOUS_UP_DOWN_COUNTER => new Aggregation\SumAggregation(),
            InstrumentType::HISTOGRAM => new Aggregation\ExplicitBucketHistogramAggregation($advisory['ExplicitBucketBoundaries'] ?? [0, 5, 10, 25, 50, 75, 100, 250, 500, 1000]),
            InstrumentType::GAUGE, InstrumentType::ASYNCHRONOUS_GAUGE => new Aggregation\LastValueAggregation(),
            default => null,
        };
    }
}
