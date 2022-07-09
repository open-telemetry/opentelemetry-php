<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Metric;

interface MetricSourceInterface
{
    /**
     * Returns the last metric collection timestamp.
     *
     * Should be used in combination with providing a `null` timestamp to
     * {@see MetricSourceInterface::collect()} to avoid active collection of
     * metrics if the last collection timestamp is within an acceptable
     * threshold.
     *
     * @return int last collection timestamp
     */
    public function collectionTimestamp(): int;

    /**
     * Collects metric data from the underlying provider.
     *
     * @param int|null $timestamp current timestamp, or null to perform no
     *        active collection
     * @return Metric collected metric
     */
    public function collect(?int $timestamp): Metric;
}
