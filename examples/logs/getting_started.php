<?php

declare(strict_types=1);

use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\EventLogger;
use OpenTelemetry\SDK\Logs\EventLoggerProvider;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogRecordProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

require __DIR__ . '/../../vendor/autoload.php';

/**
 * Example of logging used in conjunction with tracing. The trace id and span id
 * will be injected into the logged record.
 * Note that logging output is human-readable JSON, and is not compatible with the
 * OTEL format.
 */

$loggerProvider = new LoggerProvider(
    new SimpleLogRecordProcessor(
        (new ConsoleExporterFactory())->create()
    ),
    new InstrumentationScopeFactory(Attributes::factory())
);
$eventLoggerProvider = new EventLoggerProvider($loggerProvider);
$tracerProvider = new TracerProvider();
$tracer = $tracerProvider->getTracer('demo-tracer');

//start and activate a span
$span = $tracer->spanBuilder('root')->startSpan();
$scope = $span->activate();
echo 'Trace id: ' . $span->getContext()->getTraceId() . PHP_EOL;
echo 'Span id: ' . $span->getContext()->getSpanId() . PHP_EOL;

//get a logger, and emit a log record from an EventLogger. The active context (trace id + span id) will be
//attached to the log record
$eventLogger = $eventLoggerProvider->getEventLogger('demo', '1.0', 'http://schema.url', ['foo' => 'bar']);
$payload = ['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world'];
$record = (new LogRecord(['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world']))
    ->setSeverityText('INFO')
    ->setSeverityNumber(9);

$eventLogger->emit(name: 'foo', payload: $record, severityNumber: Severity::INFO);

//end span
$span->end();
$scope->detach();
