<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Instrumentation\CachedInstrumentation;

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=otlp/stdout');
putenv('OTEL_LOGS_EXPORTER=otlp/stdout');
putenv('OTEL_METRICS_EXPORTER=otlp/stdout');

require __DIR__ . '/../../../vendor/autoload.php';

$instrumentation = new CachedInstrumentation('demo');

$instrumentation->tracer()->spanBuilder('root')->startSpan()->end();
$instrumentation->meter()->createCounter('cnt')->add(1);
$instrumentation->eventLogger()->emit('foo', 'hello, otel');

echo PHP_EOL . 'OTLP/stdout autoload example complete!';
echo PHP_EOL;
