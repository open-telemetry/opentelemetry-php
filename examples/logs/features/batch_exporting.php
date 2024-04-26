<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Logs\Severity;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
use OpenTelemetry\SDK\Common\Time\ClockFactory;
use OpenTelemetry\SDK\Logs\EventLogger;
use OpenTelemetry\SDK\Logs\Exporter\ConsoleExporterFactory;
use OpenTelemetry\SDK\Logs\LoggerProvider;
use OpenTelemetry\SDK\Logs\Processor\BatchLogRecordProcessor;

require __DIR__ . '/../../../vendor/autoload.php';

$loggerProvider = new LoggerProvider(
    new BatchLogRecordProcessor(
        (new ConsoleExporterFactory())->create(),
        ClockFactory::getDefault()
    ),
    new InstrumentationScopeFactory(Attributes::factory())
);
//get a logger, and emit a log record from an EventLogger.
$loggerOne = $loggerProvider->getLogger('demo', '1.0');
$loggerTwo = $loggerProvider->getLogger('demo', '2.0');
$eventLoggerOne = new EventLogger($loggerOne);
$eventLoggerTwo = new EventLogger($loggerTwo);

$payload = ['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world'];

$eventLoggerOne->emit(name: 'foo', payload: $payload, severityNumber: Severity::INFO);
$eventLoggerOne->emit('bar', 'hello world');
$eventLoggerTwo->emit(name: 'foo', payload: $payload, severityNumber: Severity::INFO);

//shut down logger provider
$loggerProvider->shutdown();
