<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Globals;

/**
 * Define OTEL_PHP_EXCLUDED_URLS to include a pattern that matches
 * REQUEST_URI, which effectively disables the SDK autoloader for some URLs,
 * meaning no telemetry signals will be emitted. The incoming trace context is still propagated to outgoing calls.
 */
putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=console');
putenv('OTEL_PHP_EXCLUDED_URLS=client/.*/info,healthcheck');
$_SERVER['REQUEST_URI'] = 'https://example.com/v1/healthcheck';

require __DIR__ . '/../../../vendor/autoload.php';

$tracer = Globals::tracerProvider()->getTracer('demo');
$tracer->spanBuilder('healthcheck')->startSpan()->end();
