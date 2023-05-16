<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;
use OpenTelemetry\SDK\Registry;

class ConsoleMetricExporterFactory implements MetricExporterFactoryInterface
{
    public function create(): MetricExporterInterface
    {
        $transport = Registry::transportFactory('stream')->create('php://stdout', 'application/x-ndjson');

        return new MetricExporter($transport);
    }
}
