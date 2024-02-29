<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class HistogramDataPoint
{
    /**
     * @param float|int $sum
     * @param float|int $min
     * @param float|int $max
     * @param int[] $bucketCounts
     * @param list<float|int> $explicitBounds
     */
    public function __construct(
        /**
         * @readonly
         */
        public int $count,
        /**
         * @readonly
         */
        public $sum,
        /**
         * @readonly
         */
        public $min,
        /**
         * @readonly
         */
        public $max,
        /**
         * @readonly
         */
        public array $bucketCounts,
        /**
         * @readonly
         */
        public array $explicitBounds,
        /**
         * @readonly
         */
        public AttributesInterface $attributes,
        /**
         * @readonly
         */
        public int $startTimestamp,
        /**
         * @readonly
         */
        public int $timestamp,
        /**
         * @readonly
         */
        public iterable $exemplars = []
    ) {
    }
}
