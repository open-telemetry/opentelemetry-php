<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\SDK\Trace\TracerProviderFactory;

putenv('OTEL_PHP_DETECTORS=env,sdk,sdk_provided');
putenv('OTEL_TRACES_EXPORTER=console');
putenv('OTEL_RESOURCE_ATTRIBUTES=foo=bar'); //env detector will add this to trace attributes

echo 'Handling Resource Detectors From Environment' . PHP_EOL;

$tracerProvider = (new TracerProviderFactory('example'))->create();

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

echo 'Starting Tracer' . PHP_EOL;

$rootSpan = $tracer->spanBuilder('root')->startSpan();
$rootSpan->activate();
$rootSpan->end();
