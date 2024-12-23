<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final readonly class HistogramDataPoint
{
    /**
     * @param int[] $bucketCounts
     * @param list<float|int> $explicitBounds
     */
    public function __construct(
        public int $count,
        public float|int $sum,
        public float|int $min,
        public float|int $max,
        public array $bucketCounts,
        public array $explicitBounds,
        public AttributesInterface $attributes,
        public int $startTimestamp,
        public int $timestamp,
        public iterable $exemplars = [],
    ) {
    }
}
