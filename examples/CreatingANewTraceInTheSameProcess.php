<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporter;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

// Boilerplate setup to create a new tracer with console output
$tracer = (new TracerProvider(
    new SimpleSpanProcessor(
        new ConsoleSpanExporter()
    )
))->getTracer();

// This creates a span and sets it as the current parent (and root) span
$rootSpan = $tracer->spanBuilder('foo')->startSpan();
$rootScope = $rootSpan->activate();

// This creates (and closes) a child span
$childSpan = $tracer->spanBuilder('bar')->startSpan();
$childSpan->end();

// This closes the root/parent span and detaches its scope/context
$rootSpan->end();
$rootScope->detach();

// This creates a new span as a parent/root, however regardless of calling "activate" on it, it will have a new TraceId
$span = $tracer->spanBuilder('baz')->startSpan();
$scope = $span->activate();

$span->end();
$scope->detach();
