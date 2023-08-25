<?php

declare(strict_types=1);

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Instrumentation\Configurator;
use OpenTelemetry\SDK\Trace\SpanExporter\ConsoleSpanExporterFactory;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

require __DIR__ . '/../vendor/autoload.php';

/**
 * Example of globally registering <Signal>Provider instances.
 * Generally this is hidden inside the SDK builder or SDK autoloading,
 * but you can also do it manually. The providers are stored in
 * context, and reset to previous values or defaults when the
 * scope is detached.
 */

//before, a no-op provider is provided by default
echo 'Before: ' . get_class(Globals::tracerProvider()) . PHP_EOL;

$tracerProvider = TracerProvider::builder()->addSpanProcessor(
    new SimpleSpanProcessor(
        (new ConsoleSpanExporterFactory())->create()
    )
)->build();

$configurator = Configurator::create()
    ->withTracerProvider($tracerProvider);

$scope = $configurator->activate();
//activated, now our $tracerProvider is globally available
echo 'During: ' . get_class(Globals::tracerProvider()) . PHP_EOL;

$scope->detach();

//after scope detached, back to default no-op providers:
echo 'After: ' . get_class(Globals::tracerProvider()) . PHP_EOL;
