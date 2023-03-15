<?php

declare(strict_types=1);

use Monolog\Logger;
use OpenTelemetry\API\Common\Instrumentation\Globals;

/**
 * This example demonstrates using an SDK logger directly as a PSR-3 logger
 */

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_METRICS_EXPORTER=none');
putenv('OTEL_LOGS_EXPORTER=otlp');
putenv('OTEL_LOGS_PROCESSOR=batch');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://collector:4317');

require __DIR__ . '/../../../vendor/autoload.php';
$tracer = Globals::tracerProvider()->getTracer('monolog-demo');

//start a span so that logs contain span context
$span = $tracer->spanBuilder('foo')->startSpan();
$scope = $span->activate();

$psr3 = Globals::loggerProvider()->getLogger('psr3-logger');

$psr3->debug('debug message');
$psr3->info('hello world', ['extra_one' => 'value_one']);
$psr3->alert('foo', ['extra_two' => 'value_two']);
$psr3->emergency('something bad happened', ['exception' => new Exception('kaboom')]);

$scope->detach();
$span->end();
