<?php

declare(strict_types=1);

use OpenTelemetry\API\Trace\SpanKind;
use OpenTelemetry\Config\SDK\Configuration;
use OpenTelemetry\SemConv\TraceAttributes;

require dirname(__DIR__, 3) . '/vendor/autoload.php';

echo 'SpanMetrics processor from declarative config...' . PHP_EOL;

$config = Configuration::parseFile(__DIR__ . '/span_metrics.yaml');
$sdk = $config
    ->create()
    ->setAutoShutdown(true)
    ->build();

$tracer = $sdk->getTracerProvider()->getTracer('demo');

$span = $tracer
    ->spanBuilder('GET /users/{id}')
    ->setSpanKind(SpanKind::KIND_SERVER)
    ->setAttribute(TraceAttributes::HTTP_REQUEST_METHOD, 'GET')
    ->setAttribute(TraceAttributes::HTTP_ROUTE, '/users/{id}')
    ->startSpan();
$scope = $span->activate();
usleep(1200000);

$scope->detach();
$span->end();

echo 'Finished!' . PHP_EOL;
