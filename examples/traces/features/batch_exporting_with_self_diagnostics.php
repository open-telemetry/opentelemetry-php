<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Globals;

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=console');
putenv('OTEL_METRICS_EXPORTER=console');
putenv('OTEL_PHP_INTERNAL_METRICS_ENABLED=true');

require __DIR__ . '/../../../vendor/autoload.php';

/**
 * Demonstrates batch span processing which also emits metrics for the internal state
 * of the processor (eg spans received, queue length)
 */

echo 'Starting ConsoleSpanExporter with BatchSpanProcessor and metrics' . PHP_EOL;

$tracer = Globals::tracerProvider()->getTracer('io.opentelemetry.contrib.php');
$tracer->spanBuilder('root')->startSpan()->end();

echo PHP_EOL . 'Example complete!  ' . PHP_EOL;
