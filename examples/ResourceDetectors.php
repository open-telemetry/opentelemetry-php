<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\SDK\Trace\TracerProviderFactory;

putenv('OTEL_PHP_DETECTORS=env,sdk,sdk_provided');

echo 'Handling Resource Detectors From Environment' . PHP_EOL;

$tracerProvider = (new TracerProviderFactory('example'))->create();

$tracer = $tracerProvider->getTracer();

echo 'Starting Tracer' . PHP_EOL;

$rootSpan = $tracer->spanBuilder('root')->startSpan();
$rootSpan->activate();
$rootSpan->end();
