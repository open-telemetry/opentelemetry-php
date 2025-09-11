<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Instrumentation\CachedInstrumentation;
use OpenTelemetry\API\Logs\LogRecord;

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=stdout/ndjson');

require __DIR__ . '/../../../vendor/autoload.php';

$instrumentation = new CachedInstrumentation('demo');

$instrumentation->tracer()->spanBuilder('root')->startSpan()->end();
$instrumentation->meter()->createCounter('cnt')->add(1);
$instrumentation->logger()->emit(new LogRecord('foo'));

echo PHP_EOL . 'OTLP/stdout autoload example complete!';
echo PHP_EOL;
