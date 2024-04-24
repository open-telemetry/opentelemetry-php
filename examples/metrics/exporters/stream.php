<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Export\Stream\StreamTransportFactory;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;

$clock = Clock::getDefault();
// @psalm-suppress InternalMethod
$reader = new ExportingReader(
    new MetricExporter(
        (new StreamTransportFactory())->create(STDOUT, 'application/x-ndjson')
    )
);

$metricsGenerator = new ExampleMetricsGenerator($reader, $clock);
$metricsGenerator->generate();
