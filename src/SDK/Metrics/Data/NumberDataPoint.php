<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final readonly class NumberDataPoint
{
    public function __construct(
        public float|int $value,
        public AttributesInterface $attributes,
        public int $startTimestamp,
        public int $timestamp,
        public iterable $exemplars = [],
    ) {
    }
}
