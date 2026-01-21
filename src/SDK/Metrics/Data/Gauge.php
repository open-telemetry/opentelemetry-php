<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

final readonly class Gauge implements DataInterface
{
    /**
     * @param iterable<NumberDataPoint> $dataPoints
     */
    public function __construct(
        public iterable $dataPoints,
    ) {
    }
}
