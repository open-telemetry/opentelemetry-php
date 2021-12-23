<?php declare(strict_types=1);
namespace OpenTelemetry\SDK\Metrics\Aggregation;

final class ExplicitBucketHistogramSummary {

    public function __construct(
        public int $count,
        public float|int $sum,
        public array $buckets,
    ) {}
}
