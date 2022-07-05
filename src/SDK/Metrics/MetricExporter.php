<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics;

use OpenTelemetry\SDK\Metrics\Data\Metric;
use OpenTelemetry\SDK\Metrics\Data\Temporality;

interface MetricExporter
{
    public function temporality(MetricMetadata $metric): ?Temporality;

    /**
     * @param iterable<Metric> $batch
     */
    public function export(iterable $batch): bool;

    public function shutdown(): bool;

    public function forceFlush(): bool;
}
