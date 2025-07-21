<?php

declare(strict_types=1);

require 'vendor/autoload.php';

use OpenTelemetry\API\Trace\Propagation\TraceContextPropagator;
use OpenTelemetry\Context\Propagation\EnvironmentGetterSetter;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

$tracerProvider = new TracerProvider(
    new SimpleSpanProcessor(
        (new ConsoleSpanExporterFactory())->create()
    )
);
$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$propagator = TraceContextPropagator::getInstance();
$envGetterSetter = EnvironmentGetterSetter::getInstance();

$isChild = isset($argv[1]) && $argv[1] === 'child';

if (!$isChild) {
    echo 'Starting trace context environment carrier example' . PHP_EOL;

    // Start parent span
    $span = $tracer->spanBuilder('root')->startSpan();
    $spanContext = $span->getContext();
    $scope = $span->activate();

    echo '------------- Parent process trace info -------------' . PHP_EOL;
    echo 'Trace ID: ' . ($spanContext->getTraceId() ?: 'not set') . PHP_EOL;
    echo 'Span ID: ' . ($spanContext->getSpanId() ?: 'not set') . PHP_EOL;

    $carrier = [];
    // Inject trace context into environment variables
    $propagator->inject($carrier, $envGetterSetter);

    // Execute child process
    $command = 'php ' . escapeshellarg(__FILE__) . ' child';
    exec($command, $output, $return);

    echo $return === 0 ? implode(PHP_EOL, $output) . PHP_EOL : "Child process failed with code $return" . PHP_EOL;

    $scope->detach();
    $span->end();
} else {
    // Extract trace context from environment variables
    $context = $propagator->extract([], $envGetterSetter);
    $scope = $context->activate();

    // Start child span with parent context
    $span = $tracer->spanBuilder('child-span')->setParent($context)->startSpan();
    $spanContext = $span->getContext();
    $span->addEvent('Processing in child');

    echo '-------------- Child process trace info -------------' . PHP_EOL;
    echo 'Trace ID: ' . ($spanContext->getTraceId() ?: 'not set') . PHP_EOL;
    echo 'Span ID: ' . ($spanContext->getSpanId() ?: 'not set') . PHP_EOL;

    $scope->detach();
    $span->end();
}

$tracerProvider->shutdown();
