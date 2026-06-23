<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use function count;
use function is_array;

final class Gauge implements DataInterface
{
    /**
     * @param iterable<NumberDataPoint> $dataPoints
     */
    public function __construct(
        public readonly iterable $dataPoints,
    ) {
    }

    #[\Override]
    public function dataPointCount(): int
    {
        return is_array($this->dataPoints) ? count($this->dataPoints) : 0;
    }
}
