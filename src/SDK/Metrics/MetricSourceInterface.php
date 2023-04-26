<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Metric;

interface MetricSourceInterface
{
    /**
     * Returns the last metric collection timestamp.
     *
     * @return int last collection timestamp
     */
    public function collectionTimestamp(): int;

    /**
     * Collects metric data from the underlying provider.
     *
     * @return Metric collected metric
     */
    public function collect(): Metric;
}
