<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

use function count;
use function is_array;

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

    #[\Override]
    public function dataPointCount(): int
    {
        return is_array($this->dataPoints) ? count($this->dataPoints) : 0;
    }
}
