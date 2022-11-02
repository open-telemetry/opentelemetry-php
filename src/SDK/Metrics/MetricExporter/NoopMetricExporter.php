<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Metrics\MetricMetadataInterface;

class NoopMetricExporter implements MetricExporterInterface
{

    /**
     * @inheritDoc
     */
    public function temporality(MetricMetadataInterface $metric)
    {
        return $metric->temporality();
    }

    /**
     * @inheritDoc
     */
    public function export(iterable $batch): bool
    {
        return true;
    }

    public function shutdown(): bool
    {
        return true;
    }

    public function forceFlush(): bool
    {
        return true;
    }
}
