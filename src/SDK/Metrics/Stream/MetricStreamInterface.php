<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\Stream;

use OpenTelemetry\SDK\Metrics\Data\DataInterface;
use OpenTelemetry\SDK\Metrics\Data\Temporality;

/**
 * @internal
 */
interface MetricStreamInterface
{
    /**
     * Returns the internal temporality of this stream.
     *
     * @return string|Temporality internal temporality
     */
    public function temporality();

    /**
     * Returns the last metric timestamp.
     *
     * @return int metric timestamp
     */
    public function timestamp(): int;

    /**
     * Pushes metric data to the stream.
     *
     * @param Metric $metric metric data to push
     */
    public function push(Metric $metric): void;

    /**
     * Registers a new reader with the given temporality.
     *
     * @param string|Temporality $temporality temporality to use
     * @return int reader id
     */
    public function register($temporality): int;

    /**
     * Unregisters the given reader.
     *
     * @param int $reader reader id
     */
    public function unregister(int $reader): void;

    /**
     * Collects metric data for the given reader.
     *
     * @param int $reader reader id
     * @return DataInterface metric data
     */
    public function collect(int $reader): DataInterface;
}
