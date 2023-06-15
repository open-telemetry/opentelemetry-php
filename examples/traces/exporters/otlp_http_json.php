<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\API\LoggerHolder;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

LoggerHolder::set(new Logger('otlp-example', [new StreamHandler('php://stderr')]));

$transport = (new OtlpHttpTransportFactory())->create('http://collector:4318/v1/traces', 'application/json');
$exporter = new SpanExporter($transport);

echo 'Starting OTLP+json example';

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        $exporter
    )
);
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$root = $span = $tracer->spanBuilder('root')->startSpan();
$root->end();
echo PHP_EOL . 'OTLP+json example complete!  ';

echo PHP_EOL;
$tracerProvider->shutdown();
