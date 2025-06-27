<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Globals;
use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\SemConv\TraceAttributes;

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=console');
putenv('OTEL_METRICS_EXPORTER=console');
putenv('OTEL_LOGS_EXPORTER=none');
putenv('OTEL_PHP_TRACES_PROCESSOR=batch,http_metrics');

/**
 * This example uses the batch span processor to emit spans, as well as the
 * HttpMetrics span processor to emit HTTP server request metrics.
 * The batch span processor can be removed if you only want to see the
 * generated metrics.
 */

echo 'SpanMetricsProcessor example...' . PHP_EOL;

require dirname(__DIR__, 3) . '/vendor/autoload.php';

$tracer = Globals::tracerProvider()->getTracer('demo');

// a span must have kind=SERVER and http.request.method attribute to identify it as an HTTP server request
$span = $tracer
    ->spanBuilder('GET /users/{id}')
    ->setSpanKind(SpanKind::KIND_SERVER)
    ->setAttribute(TraceAttributes::HTTP_REQUEST_METHOD, 'GET')
    ->setAttribute(TraceAttributes::HTTP_ROUTE, '/users/{id}')
    ->startSpan();
$scope = $span->activate();
usleep(300000);

$scope->detach();
$span->end();

echo 'Finished!' . PHP_EOL;
