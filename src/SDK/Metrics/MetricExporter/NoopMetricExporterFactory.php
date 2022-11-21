<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

class NoopMetricExporterFactory implements MetricExporterFactoryInterface
{
    public function fromEnvironment(): MetricExporterInterface
    {
        return new NoopMetricExporter();
    }
}
