<?php

declare(strict_types=1);
require __DIR__ . '/../../../vendor/autoload.php';

\OpenTelemetry\SDK\Common\Log\LoggerHolder::set(new \Monolog\Logger('grpc', [new \Monolog\Handler\StreamHandler('php://stderr')]));

/**
 * Create an otlp+http/protobuf tracer provider from TracerProviderFactory, using environment variables as input
 */
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://collector:4318');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=http/protobuf');
$factory = new \OpenTelemetry\SDK\Trace\TracerProviderFactory();
$tracerProvider = $factory->create();

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$tracer->spanBuilder('root')->startSpan()->end();
echo PHP_EOL . 'OTLP http/protobuf example complete!  ';

echo PHP_EOL;
$tracerProvider->shutdown();
