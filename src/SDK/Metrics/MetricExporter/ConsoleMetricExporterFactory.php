<?php

declare(strict_types=1);

namespace OpenTelemetry\SDK\Metrics\MetricExporter;

use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

class ConsoleMetricExporterFactory implements MetricExporterFactoryInterface
{
    public function create(): MetricExporterInterface
    {
        return new ConsoleMetricExporter();
    }
}
