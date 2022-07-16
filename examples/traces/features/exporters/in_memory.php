<?php

declare(strict_types=1);
require __DIR__ . '/../../../../vendor/autoload.php';

use OpenTelemetry\SDK\Trace\SpanDataInterface;
use OpenTelemetry\SDK\Trace\SpanExporter\InMemoryExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

// Create an ArrayObject as the storage for the spans
$storage = new ArrayObject();

// Boilerplate setup to create a new tracer with an in-memory exporter
$tracer = (new TracerProvider(
    new SimpleSpanProcessor(
        new InMemoryExporter()
    )
))->getTracer('io.opentelemetry.contrib.php');

// This creates a span and sets it as the current parent (and root) span
$rootSpan = $tracer->spanBuilder('foo')->startSpan();
$rootScope = $rootSpan->activate();

// This creates child spans
$childSpan1 = $tracer->spanBuilder('bar')->startSpan();
$childSpan2 = $tracer->spanBuilder('bar')->startSpan();

// This closes all spans
$childSpan2->end();
$childSpan1->end();
$rootSpan->end();

/** @var SpanDataInterface $span */
foreach ($storage as $span) {
    echo PHP_EOL . sprintf(
        'TRACE: "%s", SPAN: "%s", PARENT: "%s"',
        $span->getTraceId(),
        $span->getSpanId(),
        $span->getParentSpanId()
    );
}
echo PHP_EOL;
