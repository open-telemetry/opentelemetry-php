<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Globals;

/**
 * OTEL_PHP_EXCLUDED_REQUEST_HEADERS is a list of `name: value` entries (or just `name`, to match any value).
 * A web request that has a matching header disables the SDK autoloader, like OTEL_PHP_EXCLUDED_URLS does for a URL,
 * so no telemetry signals are emitted. The name is case-insensitive and the value accepts `*` wildcards.
 * The list is comma-separated, so a value cannot contain a comma.
 *
 * Usage: php -S localhost:8080 examples/traces/features/ignore_request_headers.php
 *   curl localhost:8080                                   # spans
 *   curl -H 'User-Agent: kube-probe/1.28' localhost:8080  # nothing
 */
putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=console');
putenv('OTEL_METRICS_EXPORTER=none');
putenv('OTEL_LOGS_EXPORTER=none');
putenv('OTEL_PHP_TRANSACTION_SPAN_ENABLED=true');
putenv('OTEL_PHP_EXCLUDED_REQUEST_HEADERS=User-Agent: kube-probe*, X-Skip-Tracing');

require dirname(__DIR__, 3) . '/vendor/autoload.php';

Globals::tracerProvider()->getTracer('demo')->spanBuilder('work')->startSpan()->end();
echo 'hello world!' . PHP_EOL;
