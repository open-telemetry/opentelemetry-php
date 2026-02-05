<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

final class Histogram implements DataInterface
{
    /**
     * @param iterable<HistogramDataPoint> $dataPoints
     */
    public function __construct(
        public readonly iterable $dataPoints,
        public readonly string|Temporality $temporality,
    ) {
    }
}
