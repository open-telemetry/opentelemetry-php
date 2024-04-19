<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\API\Common\Time\ClockFactory;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;

$clock = ClockFactory::getDefault();
$reader = new ExportingReader(
    new MetricExporter(
        PsrTransportFactory::discover()->create('http://collector:4318/v1/metrics', \OpenTelemetry\Contrib\Otlp\ContentTypes::JSON)
    )
);

$metricsGenerator = new ExampleMetricsGenerator($reader, $clock);
$metricsGenerator->generate();
