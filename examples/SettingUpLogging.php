<?php

declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\Contrib\OtlpGrpc\Exporter as OtlpGrpcExporter;
use OpenTelemetry\SDK\GlobalLoggerHolder;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Psr\Log\LogLevel;

echo 'Starting SettingUpLogging example' . PHP_EOL;

//create a Logger, and register it with the global logger holder. The library will use this logger
//for all of its internal logging (errors, warnings, etc)
$logger = new Logger('otel-php', [new StreamHandler(STDOUT, LogLevel::DEBUG)]);
GlobalLoggerHolder::set($logger);

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        new OtlpGrpcExporter(), //default endpoint unavailable, so exporting will fail
    )
);
$tracer = $tracerProvider->getTracer();
$span = $tracer->spanBuilder('root-span')->startSpan();
$span->end();
