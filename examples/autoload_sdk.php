<?php

declare(strict_types=1);

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=otlp');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');
putenv('OTEL_METRICS_EXPORTER=otlp');
putenv('OTEL_EXPORTER_OTLP_METRICS_PROTOCOL=grpc');
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://collector:4317');
putenv('OTEL_PHP_TRACES_PROCESSOR=batch');
putenv('OTEL_PROPAGATORS=b3,baggage,tracecontext');

echo 'autoloading SDK example starting...' . PHP_EOL;

// Composer autoloader will execute SDK/_autoload.php which will register global instrumentation from environment configuration
require dirname(__DIR__) . '/vendor/autoload.php';

$instrumentation = new \OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation('demo');

$instrumentation->tracer()->spanBuilder('root')->startSpan()->end();
$instrumentation->meter()->createCounter('cnt')->add(1);

echo 'Finished!' . PHP_EOL;
