<?php

declare(strict_types=1);

use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use OpenTelemetry\API\Common\Log\LoggerHolder;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\Contrib\Otlp\LogsExporter;
use OpenTelemetry\Contrib\Otlp\OtlpHttpTransportFactory;
use OpenTelemetry\Contrib\Otlp\SpanExporter;
use Opentelemetry\Proto\Logs\V1\SeverityNumber;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\LogRecordLimitsBuilder;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogsProcessor;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;
use Psr\Log\LogLevel;

require __DIR__ . '/../../../vendor/autoload.php';

/**
 * This example demonstrates sending logs and traces via OTLP http/json. The logs should contain the trace id
 * and span id of the active span.
 */

echo 'Starting OTLP+json with trace example' . PHP_EOL;

LoggerHolder::set(
    new Logger('otel-php', [new StreamHandler(STDOUT, LogLevel::DEBUG)])
);

$tracerProvider =  new TracerProvider(
    new SimpleSpanProcessor(
        new SpanExporter(
            (new OtlpHttpTransportFactory())->create('http://collector:4318/v1/traces', 'application/json')
        )
    )
);

$loggerProvider = new LoggerProvider(
    new SimpleLogsProcessor(
        new LogsExporter(
            (new OtlpHttpTransportFactory())->create('http://collector:4318/v1/logs', 'application/json')
        )
    ),
    new InstrumentationScopeFactory(
        (new LogRecordLimitsBuilder())->build()->getAttributeFactory()
    )
);

$tracer = $tracerProvider->getTracer('io.opentelemetry.contrib.php');
$logger = $loggerProvider->getLogger('io.opentelemetry.contrib.php', '1.0', 'http://schema.url', true, ['extra' => 'added-to-all-logs']);

$record = (new LogRecord(['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world']))
    ->setSeverityText('INFO')
    ->setTimestamp((new DateTime())->getTimestamp() * LogRecord::NANOS_PER_SECOND)
    ->setSeverityNumber(SeverityNumber::SEVERITY_NUMBER_INFO);

$span = $tracer->spanBuilder('root')->startSpan();
$scope = $span->activate();
echo 'Trace id: ' . $span->getContext()->getTraceId() . PHP_EOL;
echo 'Span id: ' . $span->getContext()->getSpanId() . PHP_EOL;

$logger->logRecord($record);

$scope->detach();
$span->end();

echo 'Finished example' . PHP_EOL;
