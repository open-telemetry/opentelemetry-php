<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

class NoopMetricExporter implements MetricExporterInterface
{
    /**
     * @inheritDoc
     */
    #[\Override]
    public function export(iterable $batch): bool
    {
        return true;
    }

    #[\Override]
    public function shutdown(): bool
    {
        return true;
    }
}
