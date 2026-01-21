<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

final readonly class Histogram implements DataInterface
{
    /**
     * @param iterable<HistogramDataPoint> $dataPoints
     */
    public function __construct(
        public iterable $dataPoints,
        public string|Temporality $temporality,
    ) {
    }
}
