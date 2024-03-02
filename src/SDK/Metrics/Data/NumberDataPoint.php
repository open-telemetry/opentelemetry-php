<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class NumberDataPoint
{
    public function __construct(
        /** @readonly */
        public float|int $value,
        /** @readonly */
        public AttributesInterface $attributes,
        /** @readonly */
        public int $startTimestamp,
        /** @readonly */
        public int $timestamp,
        /** @readonly */
        public iterable $exemplars = [],
    ) {
    }
}
