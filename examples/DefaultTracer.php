<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use OpenTelemetry\SDK\Trace\TracerProvider;

echo 'Starting DefaultTracer example' . PHP_EOL;

//Until a tracer is created, default is a NoopTracer
$initialDefault = TracerProvider::getDefaultTracer();
echo sprintf('Initial default tracer: %s', get_class($initialDefault)) . PHP_EOL;

$tracerProvider =  new TracerProvider();

//create a tracer, which is now the default and is available from anywhere via TracerProvider::getDefaultTracer()
$tracer = $tracerProvider->getTracer();
echo sprintf('Created tracer: %s', get_class($tracer)) . PHP_EOL;

$rootSpan = $tracer->spanBuilder('root')->startSpan();
$rootSpan->activate();

$default = TracerProvider::getDefaultTracer();
echo sprintf('Default tracer: %s', get_class($default)) . PHP_EOL;
$rootSpan->end();
