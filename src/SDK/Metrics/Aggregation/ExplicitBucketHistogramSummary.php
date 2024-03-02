<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

final class ExplicitBucketHistogramSummary
{
    /**
     * @param int[] $buckets
     */
    public function __construct(
        public int $count,
        public float|int $sum,
        public float|int $min,
        public float|int $max,
        public array $buckets,
    ) {
    }
}
