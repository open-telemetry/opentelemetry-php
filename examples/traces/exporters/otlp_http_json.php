<?php

declare(strict_types=1);
require __DIR__ . '/../../../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\API\Common\Log\LoggerHolder;
use OpenTelemetry\API\Trace\StatusCode;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

LoggerHolder::set(new Logger('otlp-example', [new StreamHandler('php://stderr')]));

echo 'Starting OTLP+json example';

$tracerProvider =  new TracerProvider(
    new BatchSpanProcessor(
        new SpanExporter(
            (new OtlpHttpTransportFactory())->create('http://collector:4318/v1/traces', 'application/json')
        ),
        ClockFactory::getDefault(),
    ),
);
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$root = $span = $tracer->spanBuilder('root')->startSpan();
$scope = $root->activate();

$child = $tracer->spanBuilder('child')->startSpan();
$child->end();

$span->setStatus(StatusCode::STATUS_OK);
$root->end();
$scope->detach();
echo PHP_EOL . 'OTLP+json example complete!  ';

echo PHP_EOL;
$tracerProvider->shutdown();
