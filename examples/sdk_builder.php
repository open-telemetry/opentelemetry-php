<?php

declare(strict_types=1);

use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Contrib\Otlp\MetricExporter;
use OpenTelemetry\SDK\Common\Export\Http\PsrTransportFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Metrics\MeterProvider;
use OpenTelemetry\SDK\Metrics\MetricReader\ExportingReader;
use OpenTelemetry\SDK\Resource\ResourceInfoFactory;
use OpenTelemetry\SDK\Sdk;
use OpenTelemetry\SDK\Trace\Sampler\AlwaysOnSampler;
use OpenTelemetry\SDK\Trace\Sampler\ParentBased;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\BatchSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

require __DIR__ . '/../vendor/autoload.php';

echo 'Starting SDK builder example' . PHP_EOL;

$resource = ResourceInfoFactory::defaultResource();
$spanExporter = new InMemoryExporter();
$logRecordExporter = new \OpenTelemetry\SDK\Logs\Exporter\InMemoryExporter();

$reader = new ExportingReader(
    new MetricExporter(
        PsrTransportFactory::discover()->create('http://collector:4318/v1/metrics', 'application/x-protobuf')
    )
);

$meterProvider = MeterProvider::builder()
    ->setResource($resource)
    ->addReader($reader)
    ->build();

$loggerProvider = LoggerProvider::builder()
    ->addLogRecordProcessor(
        new SimpleLogRecordProcessor($logRecordExporter)
    )
    ->build();

$tracerProvider = TracerProvider::builder()
    ->addSpanProcessor(
        BatchSpanProcessor::builder($spanExporter)
            ->setMeterProvider($meterProvider)
            ->build()
    )
    ->setResource($resource)
    ->setSampler(new ParentBased(new AlwaysOnSampler()))
    ->build();

Sdk::builder()
    ->setTracerProvider($tracerProvider)
    ->setMeterProvider($meterProvider)
    ->setLoggerProvider($loggerProvider)
    ->setPropagator(TraceContextPropagator::getInstance())
    ->setAutoShutdown(true)
    ->buildAndRegisterGlobal();

$instrumentation = new CachedInstrumentation('example');
$tracer = $instrumentation->tracer();

$root = $tracer->spanBuilder('root')->startSpan();
$scope = $root->activate();
for ($i=0; $i < 100; $i++) {
    if ($i%8 === 0) {
        $reader->collect();
    }
    $tracer->spanBuilder('span-' . $i)
        ->startSpan()
        ->end();
    usleep(50000);
}
$scope->detach();
$root->end();
$reader->shutdown();

echo 'Finished SDK builder example' . PHP_EOL;
