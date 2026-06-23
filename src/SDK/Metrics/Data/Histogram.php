<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use function count;
use function is_array;

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

    #[\Override]
    public function dataPointCount(): int
    {
        return is_array($this->dataPoints) ? count($this->dataPoints) : 0;
    }
}
