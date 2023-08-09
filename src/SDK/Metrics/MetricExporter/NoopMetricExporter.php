<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

class NoopMetricExporter implements MetricExporterInterface
{
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
}
