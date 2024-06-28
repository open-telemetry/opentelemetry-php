<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Logs\EventLogger;
use OpenTelemetry\SDK\Logs\EventLoggerProvider;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;

require __DIR__ . '/../../../vendor/autoload.php';

$loggerProvider = new LoggerProvider(
    new BatchLogRecordProcessor(
        (new ConsoleExporterFactory())->create(),
        Clock::getDefault()
    ),
    new InstrumentationScopeFactory(Attributes::factory())
);
$eventLoggerProvider = new EventLoggerProvider($loggerProvider);
//get a logger, and emit a log record from an EventLogger.
$eventLoggerOne = $eventLoggerProvider->getEventLogger('demo', '1.0');
$eventLoggerTwo = $eventLoggerProvider->getEventLogger('demo', '2.0');

$payload = ['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world'];

$eventLoggerOne->emit(name: 'foo', body: $payload, severityNumber: Severity::INFO);
$eventLoggerOne->emit('bar', 'hello world');
$eventLoggerTwo->emit(name: 'foo', body: $payload, severityNumber: Severity::INFO);

//shut down logger provider
$loggerProvider->shutdown();
