<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

echo 'Starting ConsoleSpanExporter' . PHP_EOL;

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        new ConsoleSpanExporter()
    )
);

$tracer = $tracerProvider->getTracer();

//start a root span
$rootSpan = $tracer->spanBuilder('root')->startSpan();
//future spans will be parented to the currently active span
$rootScope = $rootSpan->activate();

try {
    $span1 = $tracer->spanBuilder('foo')->startSpan();
    $span1Scope = $span1->activate();

    try {
        $span2 = $tracer->spanBuilder('bar')->startSpan();
        echo 'OpenTelemetry welcomes PHP' . PHP_EOL;
        $span2->end();
    } finally {
        $span1Scope->detach();
        $span1->end();
    }
} catch (Throwable $t) {
    $rootSpan->recordException($t);
} finally {
    //ensure span ends and scope is detached
    $rootScope->detach();
    $rootSpan->end();
}
