<?php

declare(strict_types=1);

namespace OpenTelemetry\Example;

use OpenTelemetry\API\Common\Time\Clock;
use OpenTelemetry\API\Logs\EventLogger;
use OpenTelemetry\API\Logs\LogRecord;
use OpenTelemetry\SDK\Common\Attribute\Attributes;
use OpenTelemetry\SDK\Common\Instrumentation\InstrumentationScopeFactory;
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
//get a logger, and emit a log record from an EventLogger.
$loggerOne = $loggerProvider->getLogger('demo', '1.0');
$loggerTwo = $loggerProvider->getLogger('demo', '2.0');
$eventLoggerOne = new EventLogger($loggerOne, 'my-domain');
$eventLoggerTwo = new EventLogger($loggerTwo, 'my-domain');

$record = (new LogRecord(['foo' => 'bar', 'baz' => 'bat', 'msg' => 'hello world']))
    ->setSeverityText('INFO')
    ->setSeverityNumber(9);

$eventLoggerOne->logEvent('foo', $record);
$eventLoggerOne->logEvent('bar', (new LogRecord('hello world')));
$eventLoggerTwo->logEvent('foo', $record);

//shut down logger provider
$loggerProvider->shutdown();
