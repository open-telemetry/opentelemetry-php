<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Contrib\Newrelic\Exporter as NewrelicExporter;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OtlpGrpcExporter;
use OpenTelemetry\Contrib\OtlpHttp\Exporter as OtlpHttpExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

echo 'Starting OTEL logging example' . PHP_EOL;

$logger = new Logger('otel-php');
$logger->pushHandler(new StreamHandler(STDOUT, Logger::DEBUG));

//OPTION 1: add the logger everywhere it needs to be...setLogger() is chainable for convenience
$spanProcessor = new MultiSpanProcessor(
    new SimpleSpanProcessor(OtlpGrpcExporter::fromConnectionString('localhost:4317')->setLogger($logger)),
    new SimpleSpanProcessor(OtlpHttpExporter::fromConnectionString()->setLogger($logger)),
    new SimpleSpanProcessor(NewrelicExporter::fromConnectionString('http://localhost:9999', 'newrelic', 'fake-key')->setLogger($logger)),
    new SimpleSpanProcessor(ZipkinExporter::fromConnectionString('http://localhost:9411/v1/spans', 'zipkin')->setLogger($logger)),
    new SimpleSpanProcessor(JaegerExporter::fromConnectionString('http://localhost:9999/jaeger', 'jaeger')->setLogger($logger)),
);
$tracerProvider =  new TracerProvider($spanProcessor);

//OPTION 2: setLogger at a high level (tracer provider, metrics provider etc) is responsible for propagating the
//logger down through its dependencies
$tracerProvider->setLogger($logger);

$tracer = $tracerProvider->getTracer();

$rootSpan = $tracer->spanBuilder('root')->startSpan();
$rootSpan->activate();

$rootSpan->end();
