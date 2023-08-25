<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

require __DIR__ . '/../../vendor/autoload.php';

use Monolog\Logger;
use OpenTelemetry\SDK\Trace\TracerProviderFactory;

echo 'Starting Logging example' . PHP_EOL;

/**
 * How to configure error output from OpenTelemetry.
 * Errors and warnings from the SDK itself (for example misconfiguration, exporter errors) will be logged through
 * the chosen mechanism.
 * Valid values for OTEL_PHP_LOG_DESTINATION: error_log, stdout, stderr, none, psr3, default
 * (default = psr-3 if LoggerHolder::set called, otherwise error_log
 *
 * Note that PSR-3 logging will only work if a value PSR-3 logger is configured by OpenTelemetry\API\LoggerHolder::set()
 * If no PSR-3 logger is available, it will fall back to using error_log.
 */

putenv('OTEL_PHP_LOG_DESTINATION=stderr');
putenv('OTEL_EXPORTER_OTLP_ENDPOINT=http://does-not-exist/endpoint'); //invalid endpoint, export will fail
putenv('OTEL_EXPORTER_OTLP_PROTOCOL=grpc');

$factory = new TracerProviderFactory();
$tracerProvider = $factory->create();

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');
$span = $tracer->spanBuilder('root-span')->startSpan();
$span->end();
$tracerProvider->shutdown();
