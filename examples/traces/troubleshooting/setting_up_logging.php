<?php

declare(strict_types=1);
require __DIR__ . '/../../../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\API\Common\Log\LoggerHolder;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;
use Psr\Log\LogLevel;

echo 'Starting SettingUpLogging example' . PHP_EOL;

//By default, opentelemetry's internal logging (errors, warnings, etc) will be output to console.
//You can instead provide a psr-3 logger to provide greater control of logging output:
LoggerHolder::set(
    new Logger('otel-php', [new StreamHandler(STDOUT, LogLevel::DEBUG)])
);
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://does-not-exist/endpoint'); //invalid endpoint, export will fail
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');
$factory = new TracerProviderFactory();
$tracerProvider = $factory->create();

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');
$span = $tracer->spanBuilder('root-span')->startSpan();
$span->end();
$tracerProvider->shutdown();
