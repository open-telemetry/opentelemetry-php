<?php

declare(strict_types=1);

namespace OpenTelemetry\Contrib\Otlp;

use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Metrics\MetricExporterFactoryInterface;
use OpenTelemetry\SDK\Metrics\MetricExporterInterface;

class StdoutMetricExporterFactory implements MetricExporterFactoryInterface
{
    public function create(): MetricExporterInterface
    {
        $transport = (new StreamTransportFactory())->create('php://stdout', ContentTypes::NDJSON);

        return new MetricExporter($transport);
    }
}
