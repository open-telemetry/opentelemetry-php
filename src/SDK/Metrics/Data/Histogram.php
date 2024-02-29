<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

final class Histogram implements DataInterface
{
    /**
     * @param iterable<HistogramDataPoint> $dataPoints
     */
    public function __construct(
        /** @readonly */
        public iterable $dataPoints,
        /** @readonly */
        public string|Temporality $temporality
    ) {
    }
}
