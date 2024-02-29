<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Data;

final class Sum implements DataInterface
{

    /**
     * @param iterable<NumberDataPoint> $dataPoints
     * @param string|Temporality $temporality
     */
    public function __construct(
        /**
         * @readonly
         */
        public iterable $dataPoints,
        /**
         * @readonly
         */
        public $temporality,
        /**
         * @readonly
         */
        public bool $monotonic
    ) {
    }
}
