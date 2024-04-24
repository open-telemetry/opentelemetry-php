<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;

$clock = Clock::getDefault();

$reader = new ExportingReader(
    new MetricExporter(
        (new GrpcTransportFactory())->create('http://collector:4317' . OtlpUtil::method(Signals::METRICS))
    )
);

$metricsGenerator = new ExampleMetricsGenerator($reader, $clock);
$metricsGenerator->generate();
