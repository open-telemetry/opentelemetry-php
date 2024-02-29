<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Globals;
use OpenTelemetry\SDK\Trace\TracerProviderInterface;

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=console');
putenv('OTEL_METRICS_EXPORTER=console');
putenv('OTEL_PHP_INTERNAL_METRICS_ENABLED=true');

require __DIR__ . '/../../../vendor/autoload.php';

$tracerProvider = Globals::tracerProvider();

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');

$span = $tracer->spanBuilder('root')->startSpan();
$span->end();

echo PHP_EOL;
$tracerProvider instanceof TracerProviderInterface && $tracerProvider->shutdown();
