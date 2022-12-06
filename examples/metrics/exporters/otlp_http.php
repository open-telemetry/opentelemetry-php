<?php

declare(strict_types=1);

require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Example\ExampleMetricsGenerator;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;

$clock = ClockFactory::getDefault();
$reader = new ExportingReader(
    new MetricExporter(
        PsrTransportFactory::discover()->create('http://collector:4318/v1/metrics', \OpenTelemetry\Contrib\Otlp\ContentTypes::JSON)
    ),
    $clock
);

$metricsGenerator = new ExampleMetricsGenerator($reader, $clock);
$metricsGenerator->generate();
