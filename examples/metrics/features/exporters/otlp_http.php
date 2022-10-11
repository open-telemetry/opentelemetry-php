<?php

declare(strict_types=1);

require __DIR__ . '/../../../../vendor/autoload.php';

use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\Protocols;
use OpenTelemetry\Example\ExampleMetricsGenerator;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;

$clock = ClockFactory::getDefault();
$reader = new ExportingReader(
    new MetricExporter(
        PsrTransportFactory::discover()->withSignal(Signals::METRICS)->create('http://collector:4318/v1/metrics'),
        Protocols::HTTP_JSON //or Protocols::HTTP_PROTOBUF
    ),
    $clock
);

$metricsGenerator = new ExampleMetricsGenerator($reader, $clock);
$metricsGenerator->generate();
