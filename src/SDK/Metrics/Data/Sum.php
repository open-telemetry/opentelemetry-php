<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

final class Sum implements Data
{

    /**
     * @param iterable<NumberDataPoint> $dataPoints
     */
    public function __construct(
        public readonly iterable $dataPoints,
        public readonly Temporality $temporality,
        public readonly bool $monotonic,
    ) {
    }
}
