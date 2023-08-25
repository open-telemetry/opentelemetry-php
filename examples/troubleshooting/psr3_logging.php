<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../vendor/autoload.php';

use OpenTelemetry\SDK\Trace\TracerProviderFactory;

echo 'Starting PSR-3 Logging example' . PHP_EOL;

putenv('OTEL_PHP_LOG_DESTINATION=default'); //or "psr3"
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://does-not-exist/endpoint'); //invalid endpoint, export will fail
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');

$filename = __DIR__ . '/var/otel.log';
\OpenTelemetry\API\LoggerHolder::set(new \Monolog\Logger('grpc', [new \Monolog\Handler\StreamHandler($filename)]));

$factory = new TracerProviderFactory();
$tracerProvider = $factory->create();

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');
$span = $tracer->spanBuilder('root-span')->startSpan();
$span->end();
$tracerProvider->shutdown();

echo sprintf("Logs written to: %s\n", $filename);
