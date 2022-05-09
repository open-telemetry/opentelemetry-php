<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\Context\ContextStorage;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

/**
 * This example demonstrates how to have multiple traces occurring within the one process, by creating custom storage
 * for each trace to use. This might be used in a situation where one process can service multiple requests
 * e.g react/swoole/roadrunner.
 * Although this example uses the default storage for one of the traces, in practise you would either use only the default
 * storage (eg in a shared-nothing webserver or single-task CLI process), or you would use only individual storages.
 */
echo 'Starting ConsoleSpanExporter with concurrent traces' . PHP_EOL;

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        new ConsoleSpanExporter()
    )
);
$storage = ContextStorage::create('custom');
$tracer = $tracerProvider->getTracer();

//start and activate a root span in $storage
$traceOneRootSpan = $tracer->spanBuilder('trace-one-root')->setStorage($storage)->startSpan();
$traceOneRootSpan->activate();

//start and activate a root span in default storage
$traceTwoRootSpan = $tracer->spanBuilder('trace-two-root')->startSpan();
$traceTwoRootSpan->activate();

//start a child span, which will have a parent of the active node from $storage
$childSpanOne = $tracer->spanBuilder('child-span-one')->setStorage($storage)->startSpan();

//start another child span, which will have a parent of the active node from default storage
$childSpanTwo = $tracer->spanBuilder('child-span-two')->startSpan();

$childSpanOne->end();
$childSpanTwo->end();

$traceOneRootSpan->end();
$traceTwoRootSpan->end();
