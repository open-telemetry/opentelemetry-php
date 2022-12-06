<?php

declare(strict_types=1);

use OpenTelemetry\SDK\Trace\TracerProviderFactory;

require __DIR__ . '/../../../vendor/autoload.php';

\OpenTelemetry\SDK\Common\Log\LoggerHolder::set(new \Monolog\Logger('grpc', [new \Monolog\Handler\StreamHandler('php://stderr')]));

/**
 * Create an otlp+grpc tracer provider from TracerProviderFactory, using environment variables as input
 */
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://collector:4317');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');
$factory = new TracerProviderFactory();
$tracerProvider = $factory->create();

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$root = $span = $tracer->spanBuilder('root')->startSpan();
$root->end();
echo PHP_EOL . 'OTLP GRPC example complete!  ';

echo PHP_EOL;
$tracerProvider->shutdown();
