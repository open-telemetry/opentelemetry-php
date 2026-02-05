<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

final class Sum implements DataInterface
{
    /**
     * @param iterable<NumberDataPoint> $dataPoints
     */
    public function __construct(
        public readonly iterable $dataPoints,
        public readonly string|Temporality $temporality,
        public readonly bool $monotonic,
    ) {
    }
}
