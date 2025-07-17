<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Common\Export\InMemoryStorageManager;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

class InMemoryExporterFactory implements MetricExporterFactoryInterface
{
    #[\Override]
    public function create(): MetricExporterInterface
    {
        return new InMemoryExporter(InMemoryStorageManager::metrics());
    }
}
