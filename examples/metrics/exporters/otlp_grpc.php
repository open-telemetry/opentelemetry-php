<?php

declare(strict_types=1);

require __DIR__ . '/../../../vendor/autoload.php';

use OpenTelemetry\API\Common\Signal\Signals;
use OpenTelemetry\Contrib\Grpc\GrpcTransportFactory;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\Contrib\Otlp\OtlpUtil;
use OpenTelemetry\Example\ExampleMetricsGenerator;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;

\OpenTelemetry\API\Common\Log\LoggerHolder::set(new \Monolog\Logger('grpc', [new \Monolog\Handler\StreamHandler('php://stderr')]));

$clock = ClockFactory::getDefault();

$reader = new ExportingReader(
    new MetricExporter(
        (new GrpcTransportFactory())->create('http://collector:4317' . OtlpUtil::method(Signals::METRICS))
    ),
);

$metricsGenerator = new ExampleMetricsGenerator($reader, $clock);
$metricsGenerator->generate();
