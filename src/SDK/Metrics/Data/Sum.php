<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

final class Sum implements DataInterface
{
    /**
     * @param iterable<NumberDataPoint> $dataPoints
     */
    public function __construct(
        /** @readonly */
        public iterable $dataPoints,
        /** @readonly */
        public string|Temporality $temporality,
        /** @readonly */
        public bool $monotonic,
    ) {
    }
}
