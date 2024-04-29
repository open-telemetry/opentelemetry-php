<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../../vendor/autoload.php';

/**
 * Create an otlp+http/protobuf tracer provider from TracerProviderFactory, using environment variables as input
 */
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://collector:4318');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=http/protobuf');
putenv('OTEL_EXPORTER_OTLP_TIMEOUT=1500'); //1.5s
$factory = new \OpenTelemetry\SDK\Trace\TracerProviderFactory();
$tracerProvider = $factory->create();

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$tracer->spanBuilder('root')->startSpan()->end();
echo PHP_EOL . 'OTLP http/protobuf example complete!  ';

echo PHP_EOL;
$tracerProvider->shutdown();
