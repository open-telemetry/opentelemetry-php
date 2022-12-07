<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LogLevel;

putenv('OTEL_PHP_AUTOLOAD_ENABLED=true');
putenv('OTEL_TRACES_EXPORTER=otlp');
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');
putenv('OTEL_METRICS_EXPORTER=otlp');
putenv('OTEL_EXPORTER_OTLP_METRICS_PROTOCOL=grpc');
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://collector:4317');
putenv('OTEL_PHP_TRACES_PROCESSOR=batch');

echo 'autoloading SDK example starting...' . PHP_EOL;

// Composer autoloader will execute SDK/_autoload.php which will register global instrumentation from environment configuration
require dirname(__DIR__) . '/vendor/autoload.php';

//if required, register logger initializer, which SDK autoloading does not do
\OpenTelemetry\API\Common\Instrumentation\Globals::registerInitializer(function (\OpenTelemetry\API\Common\Instrumentation\Configurator $configurator) {
    $logger = new Logger('otel-autoload-example', [new StreamHandler(STDOUT, LogLevel::DEBUG)]);

    return $configurator->withLogger($logger);
});

$instrumentation = new \OpenTelemetry\API\Common\Instrumentation\CachedInstrumentation('demo');

$instrumentation->tracer()->spanBuilder('root')->startSpan()->end();
$instrumentation->meter()->createCounter('cnt')->add(1);

echo 'Finished!' . PHP_EOL;
