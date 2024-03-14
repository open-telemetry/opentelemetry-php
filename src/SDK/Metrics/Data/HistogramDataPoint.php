<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class HistogramDataPoint
{
    /**
     * @param int[] $bucketCounts
     * @param list<float|int> $explicitBounds
     */
    public function __construct(
        public readonly int $count,
        public readonly float|int $sum,
        public readonly float|int $min,
        public readonly float|int $max,
        public readonly array $bucketCounts,
        public readonly array $explicitBounds,
        public readonly AttributesInterface $attributes,
        public readonly int $startTimestamp,
        public readonly int $timestamp,
        public readonly iterable $exemplars = [],
    ) {
    }
}
