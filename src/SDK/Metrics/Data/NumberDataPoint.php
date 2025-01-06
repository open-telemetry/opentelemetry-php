<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use OpenTelemetry\SDK\Common\Attribute\AttributesInterface;

final class NumberDataPoint
{
    public function __construct(
        public readonly float|int $value,
        public readonly AttributesInterface $attributes,
        public readonly int $startTimestamp,
        public readonly int $timestamp,
        public readonly iterable $exemplars = [],
    ) {
    }
}
