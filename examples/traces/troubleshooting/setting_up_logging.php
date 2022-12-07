<?php

declare(strict_types=1);
require __DIR__ . '/../../../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\API\Common\Instrumentation\Configurator;
use OpenTelemetry\API\Common\Instrumentation\Globals;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use Psr\Log\LogLevel;

echo 'Starting SettingUpLogging example' . PHP_EOL;

//create a Logger, and register it globally. The library will use this logger for logging runtime errors and warnings
Globals::registerInitializer(function (Configurator $configurator) {
    $logger = new Logger('otel-php', [new StreamHandler(STDOUT, LogLevel::DEBUG)]);

    return $configurator->withLogger($logger);
});
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://does-not-exist/endpoint'); //invalid endpoint, export will fail
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');
$factory = new TracerProviderFactory();
$tracerProvider = $factory->create();

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');
$span = $tracer->spanBuilder('root-span')->startSpan();
$span->end();
$tracerProvider->shutdown();
