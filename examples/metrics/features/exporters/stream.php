<?php

declare(strict_types=1);

require __DIR__ . '/../../../../vendor/autoload.php';

use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\Protocols;
use OpenTelemetry\Example\ExampleMetricsGenerator;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransport;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;

$clock = ClockFactory::getDefault();
$reader = new ExportingReader(
    new MetricExporter(
        new StreamTransport(STDOUT),
        Protocols::HTTP_ND_JSON
    ),
    $clock
);

$metricsGenerator = new ExampleMetricsGenerator($reader, $clock);
$metricsGenerator->generate();
