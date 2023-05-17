<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../vendor/autoload.php';

use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

echo 'Starting ConsoleSpanExporter' . PHP_EOL;

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        (new ConsoleSpanExporterFactory())->create()
    )
);

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

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
} catch (\Throwable $t) {
    //The library's code shouldn't be throwing unhandled exceptions (it should emit any errors via diagnostic events)
    //This is intended to illustrate a way you can capture unhandled exceptions coming from your app code
    $rootSpan->recordException($t);
} finally {
    //ensure span ends and scope is detached
    $rootScope->detach();
    $rootSpan->end();
}
$tracerProvider->shutdown();
