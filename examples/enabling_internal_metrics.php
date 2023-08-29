<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Globals;

/**
 * The OpenTelemetry SDK is able to emit some metrics about its internal state. For example,
 * batch span and log processor state.
 * This feature can be enabled via the OTEL_PHP_INTERNAL_METRICS_ENABLED setting.
 */

putenv('OTEL_PHP_INTERNAL_METRICS_ENABLED=true');
putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=console');
putenv('OTEL_METRICS_EXPORTER=console');
putenv('OTEL_LOGS_EXPORTER=console');

require __DIR__ . '/../vendor/autoload.php';

$tracerProvider = Globals::tracerProvider();
$tracerProvider->getTracer('demo')->spanBuilder('root')->startSpan()->end();
