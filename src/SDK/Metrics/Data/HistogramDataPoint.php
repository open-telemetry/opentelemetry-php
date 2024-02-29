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
        /** @readonly */
        public int $count,
        /** @readonly */
        public float|int $sum,
        /** @readonly */
        public float|int $min,
        /** @readonly */
        public float|int $max,
        /** @readonly */
        public array $bucketCounts,
        /** @readonly */
        public array $explicitBounds,
        /** @readonly */
        public AttributesInterface $attributes,
        /** @readonly */
        public int $startTimestamp,
        /** @readonly */
        public int $timestamp,
        /** @readonly */
        public iterable $exemplars = []
    ) {
    }
}
