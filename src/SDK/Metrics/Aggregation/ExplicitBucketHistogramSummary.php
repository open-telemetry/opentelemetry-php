<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Aggregation;

final class ExplicitBucketHistogramSummary
{
    public int $count;
    /**
     * @var float|int
     */
    public $sum;
    /**
     * @var float|int
     */
    public $min;
    /**
     * @var float|int
     */
    public $max;
    /**
     * @var int[]
     */
    public array $buckets;
    /**
     * @param float|int $sum
     * @param float|int $min
     * @param float|int $max
     * @param int[] $buckets
     */
    public function __construct(int $count, $sum, $min, $max, array $buckets)
    {
        $this->count = $count;
        $this->sum = $sum;
        $this->min = $min;
        $this->max = $max;
        $this->buckets = $buckets;
    }
}
