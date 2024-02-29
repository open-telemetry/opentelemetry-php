<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

final class ExplicitBucketHistogramSummary
{
    /**
     * @param float|int $sum
     * @param float|int $min
     * @param float|int $max
     * @param int[] $buckets
     */
    public function __construct(public int $count, public $sum, public $min, public $max, public array $buckets)
    {
    }
}
