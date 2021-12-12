<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\Contrib\Jaeger\Exporter as JaegerExporter;
use OpenTelemetry\Contrib\Newrelic\Exporter as NewrelicExporter;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OtlpGrpcExporter;
use OpenTelemetry\Contrib\OtlpHttp\Exporter as OtlpHttpExporter;
use OpenTelemetry\Contrib\Zipkin\Exporter as ZipkinExporter;
use OpenTelemetry\SDK\Trace\SpanExporter\LoggerExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\MultiSpanProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use Psr\Log\LogLevel;

echo 'Starting OTEL logging example using LoggerAwareInterface and LoggerAwareTrait' . PHP_EOL;

//EXAMPLE 1: add the logger everywhere it needs to be - setLogger() is chainable for convenience
echo 'Example 1: create from SDK, add loggers to everything' . PHP_EOL;
$loggerOne = new Logger('otel-one');
$loggerOne->pushHandler(new StreamHandler(STDOUT, Logger::DEBUG));

$spanProcessor = new MultiSpanProcessor(
    (new SimpleSpanProcessor(OtlpGrpcExporter::fromConnectionString('localhost:4317')->setLogger($loggerOne)))->setLogger($loggerOne),
    (new SimpleSpanProcessor(OtlpHttpExporter::fromConnectionString()->setLogger($loggerOne)))->setLogger($loggerOne),
    (new SimpleSpanProcessor(NewrelicExporter::fromConnectionString('http://newrelic.localhost:9999', 'newrelic', 'fake-key')->setLogger($loggerOne)))->setLogger($loggerOne),
    (new SimpleSpanProcessor(ZipkinExporter::fromConnectionString('http://zipkin.localhost:9411/v1/spans', 'zipkin')->setLogger($loggerOne)))->setLogger($loggerOne),
    (new SimpleSpanProcessor(JaegerExporter::fromConnectionString('http://jaeger.localhost:9999/jaeger', 'jaeger')->setLogger($loggerOne)))->setLogger($loggerOne),
    (new SimpleSpanProcessor(LoggerExporter::fromConnectionString('php://stdout', 'stdout-logger', LogLevel::INFO)->setLogger($loggerOne)))->setLogger($loggerOne),
);
$spanProcessor->setLogger($loggerOne);
$tracerProviderOne =  new TracerProvider($spanProcessor);
$tracerOne = $tracerProviderOne->getTracer('one');
$spanOne = $tracerOne->spanBuilder('span-one')->startSpan();
$spanOne->end();

//USE CASE 2: factories take care of all logger injection
echo 'Example 2: create from factory' . PHP_EOL;
$loggerTwo = new Logger('otel-two');
$loggerTwo->pushHandler(new StreamHandler(STDOUT, LOGGER::DEBUG));
putenv('OTEL_TRACES_SAMPLER=always_on');
putenv('OTEL_TRACES_EXPORTER=otlp');
putenv('OTEL_EXPORTER_OTLP_TRACES_PROTOCOL=grpc');
putenv('OTEL_PHP_TRACES_PROCESSOR=batch');

$tracerProviderTwo = (new TracerProviderFactory('example'))->setLogger($loggerTwo)->create();

$tracerTwo = $tracerProviderTwo->getTracer();

$spanTwo = $tracerTwo->spanBuilder('span-two')->startSpan();
$spanTwo->end();
