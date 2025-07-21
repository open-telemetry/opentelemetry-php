<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../vendor/autoload.php';

use OpenTelemetry\API\Baggage\Baggage;
use OpenTelemetry\API\Baggage\Propagation\BaggagePropagator;
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

$propagator = BaggagePropagator::getInstance();
$envGetterSetter = EnvironmentGetterSetter::getInstance();

$isChild = isset($argv[1]) && $argv[1] === 'child';

if (!$isChild) {
    echo 'Starting baggage environment carrier example' . PHP_EOL;

    // Start parent span and set baggage
    $span = $tracer->spanBuilder('root')->startSpan();
    $scope = $span->activate();
    $baggage = Baggage::getBuilder()
        ->set('key1', 'value1')
        ->set('key2', 'value2')
        ->build();
    $baggageScope = $baggage->activate();

    echo '------------- Parent process baggage info -------------' . PHP_EOL;
    echo 'key1: ' . ($baggage->getValue('key1') ?? 'not set') . PHP_EOL;
    echo 'key2: ' . ($baggage->getValue('key2') ?? 'not set') . PHP_EOL;

    $carrier = [];
    // Inject baggage into environment variables
    $propagator->inject($carrier, $envGetterSetter);

    // Execute child process
    $command = 'php ' . escapeshellarg(__FILE__) . ' child';
    exec($command, $output, $return);

    echo $return === 0 ? implode(PHP_EOL, $output) . PHP_EOL : "Child process failed with code $return" . PHP_EOL;

    $baggageScope->detach();
    $scope->detach();
    $span->end();
} else {
    // Extract baggage from environment variables
    $context = $propagator->extract([], $envGetterSetter);
    $scope = $context->activate();

    // Start child span and retrieve baggage
    $span = $tracer->spanBuilder('child-span')->setParent($context)->startSpan();
    $baggage = Baggage::getCurrent();

    // Add baggage to span attributes
    $span->setAttribute('app.key1', $baggage->getValue('key1') ?? 'not set');
    $span->setAttribute('app.key2', $baggage->getValue('key2') ?? 'not set');

    echo '------------- Child process baggage info -------------' . PHP_EOL;
    echo 'key1: ' . ($baggage->getValue('key1') ?? 'not set') . PHP_EOL;
    echo 'key2: ' . ($baggage->getValue('key2') ?? 'not set') . PHP_EOL;

    $scope->detach();
    $span->end();
}

$tracerProvider->shutdown();
