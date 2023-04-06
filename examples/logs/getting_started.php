<?php

declare(strict_types=1);

use OpenTelemetry\API\Logs\EventLogger;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\SimpleLogsProcessor;
use OpenTelemetry\SDK\Trace\TracerProvider;

require __DIR__ . '/../../vendor/autoload.php';

$processor = new SimpleLogsProcessor(
    (new ConsoleExporterFactory())->create()
);
$loggerProvider = new LoggerProvider(
    [$processor],
    new InstrumentationScopeFactory(Attributes::factory())
);
$tracerProvider = new TracerProvider();
$tracer = $tracerProvider->getTracer('demo-tracer');

//start and activate a span
$span = $tracer->spanBuilder('root')->startSpan();
$scope = $span->activate();
echo 'Trace id: ' . $span->getContext()->getTraceId() . PHP_EOL;
echo 'Span id: ' . $span->getContext()->getSpanId() . PHP_EOL;

//get a logger, and emit a log record from an EventLogger. The active context (trace id + span id) will be
//attached to the log record
$logger = $loggerProvider->getLogger('demo', '1.0', 'http://schema.url', true, ['foo' => 'bar']);
$eventLogger = new EventLogger($logger, 'my-domain');

$record = (new LogRecord(['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world']))
    ->setSeverityText('INFO')
    ->setSeverityNumber(9);

$eventLogger->logEvent('foo', $record);

//end span
$span->end();
$scope->detach();
