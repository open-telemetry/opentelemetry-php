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
    $span = $tracer->spanBuilder('root')->startSpan();
    $scope = $span->activate();

    // Inject trace context into environment variables
    $carrier = [];
    $propagator->inject($carrier, $envGetterSetter);

    // Execute child process
    $command = sprintf('%s %s %s', PHP_BINARY, escapeshellarg(__FILE__), 'child');
    pclose(popen($command, 'w'));

    $scope->detach();
    $span->end();
} else {
    // Extract trace context from environment variables
    $context = $propagator->extract([], $envGetterSetter);
    $scope = $context->activate();

    // Start child span with parent context
    $span = $tracer->spanBuilder('child-span')->setParent($context)->startSpan();
    $spanContext = $span->getContext();

    $scope->detach();
    $span->end();
}

$tracerProvider->shutdown();
